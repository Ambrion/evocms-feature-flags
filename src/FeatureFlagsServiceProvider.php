<?php declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags;

use EvolutionCMS\FeatureFlags\Application\Service\FlagStatisticsService;
use EvolutionCMS\FeatureFlags\Domain\Repository\FlagAdminRepositoryInterface;
use EvolutionCMS\FeatureFlags\Domain\Repository\FlagStatisticsRepositoryInterface;
use EvolutionCMS\FeatureFlags\Infrastructure\Logger\DatabaseFlagUsageLogger;
use EvolutionCMS\FeatureFlags\Infrastructure\Database\Models\EloquentFeatureFlag;
use EvolutionCMS\FeatureFlags\Infrastructure\Model\EloquentFlagStatistics;
use EvolutionCMS\FeatureFlags\Infrastructure\Repository\ConfigFileFlagRepository;
use EvolutionCMS\FeatureFlags\Infrastructure\Repository\ConfigFlagAdminRepository;
use EvolutionCMS\FeatureFlags\Infrastructure\Repository\EloquentFlagRepository;
use EvolutionCMS\FeatureFlags\Infrastructure\Repository\EloquentFlagStatisticsRepository;
use EvolutionCMS\FeatureFlags\Infrastructure\Repository\FlagAdminEloquentRepository;
use EvolutionCMS\ServiceProvider;
use FeatureFlags\Core\Application\Service\FeatureFlagService;
use FeatureFlags\Core\Domain\Logger\FlagUsageLoggerInterface;
use FeatureFlags\Core\Domain\Logger\NullFlagUsageLogger;
use FeatureFlags\Core\Domain\Repository\FlagRepositoryInterface;
use FeatureFlags\Core\Domain\Specification\CategorySpecification;
use FeatureFlags\Core\Domain\Specification\CompositeSpecification;
use FeatureFlags\Core\Domain\Specification\DateBetweenSpecification;
use FeatureFlags\Core\Domain\Specification\PercentageSpecification;
use FeatureFlags\Core\Domain\Specification\TargetIdSpecification;
use FeatureFlags\Core\Domain\Specification\UserRoleSpecification;
use Illuminate\Contracts\Config\Repository as ConfigContract;

class FeatureFlagsServiceProvider extends ServiceProvider
{
    protected string $namespace = 'featureFlags';
    private ?array $testFlagsConfig = null;
    private ?string $testDriver = null;

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', $this->namespace);
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', $this->namespace);
    }

    public function setConfig(array $config, ?string $driver = null): void
    {
        $this->testFlagsConfig = $config;
        $this->testDriver = $driver;
    }

    public function register(): void
    {
        // 0. Публикуем конфиг для правил и всё что пригодится
        $this->publishes([
            __DIR__ . '/../publishable/assets'  => MODX_BASE_PATH . 'assets',
            __DIR__ . '/../publishable/core'  => MODX_BASE_PATH . 'core',
        ]);

        // 1. Сначала загружаем конфиг (ОБЯЗАТЕЛЬНО до биндингов!)
        $configPath = __DIR__ . '/../config/feature_flags_config.php';
        if (file_exists($configPath) && $this->app->bound('config')) {
            $config = $this->app->make('config');
            if ($config instanceof ConfigContract) {
                $this->mergeConfigFrom($configPath, 'feature_flags');
            }
        }

        // 2. Биндим репозиторий статистики
        $this->app->singleton(FlagStatisticsRepositoryInterface::class, function () {
            return new EloquentFlagStatisticsRepository(new EloquentFlagStatistics());
        });

        $this->app->singleton(FlagStatisticsService::class, function ($app) {
            return new FlagStatisticsService($app->make(FlagStatisticsRepositoryInterface::class));
        });

        // 3. Биндим логгер
        $this->app->singleton(FlagUsageLoggerInterface::class, function ($app) {
            $config = $app->make('config');
            $logToDb = $config instanceof ConfigContract
                ? $config->get('feature_flags.log_statistics', false)
                : false;

            return $logToDb
                ? new DatabaseFlagUsageLogger(
                    $app->make(FlagStatisticsRepositoryInterface::class),
                    $app->make(FlagAdminRepositoryInterface::class),
                )
                : new NullFlagUsageLogger();
        });

        // 4. Биндим основной репозиторий флагов (для фронтенда/сниппетов)
        $this->app->bind(FlagRepositoryInterface::class, function () {
            $driver = $this->resolveDriver();
            return match ($driver) {
                'eloquent' => $this->createEloquentFlagRepository(),
                default    => $this->createConfigFlagRepository(), // 'config'
            };
        });

        // 5. Биндим сервис оценки флагов
        $this->app->singleton(FeatureFlagService::class, function ($app) {
            return new FeatureFlagService(
                $app->make(FlagRepositoryInterface::class),
                $app->make(FlagUsageLoggerInterface::class)
            );
        });

        // 6. Регистрация роутов модуля
        if (method_exists($this->app, 'registerRoutingModule')) {
            $this->app->registerRoutingModule(
                'Feature Flags',
                __DIR__ . '/../routes/module.php',
                'fa fa-flag'
            );
        }

        // 7. Биндим админ-репозиторий (для CRUD в админке)
        // Используем тот же ключ 'driver' для консистентности!
        $this->app->singleton(FlagAdminRepositoryInterface::class, function ($app) {
            $config = $app->make('config');
            $driver = $config instanceof ConfigContract
                ? $config->get('feature_flags.driver', 'config')
                : 'config';

            return match ($driver) {
                'config' => new ConfigFlagAdminRepository(
                    $this->resolveRulesConfigPath($config)
                ),
                default => new FlagAdminEloquentRepository(new EloquentFeatureFlag())
            };
        });
    }

    /**
     * Резолвер пути к файлу правил с многоуровневым фоллбэком.
     */
    private function resolveRulesConfigPath(ConfigContract $config): string
    {
        // 1. Явный путь из конфига (уже содержит env() + дефолт)
        $path = $config->get('feature_flags.config_path');

        if ($path && !str_starts_with($path, '/') && !str_starts_with($path, '\\')) {
            $basePath = defined('MODX_BASE_PATH') ? rtrim(MODX_BASE_PATH, '/\\') : '';
            $path = $basePath . '/' . ltrim($path, '/\\');
        }

        // 2. Если файл существует — используем его
        if ($path && file_exists($path)) {
            return $path;
        }

        // 3. Fallback на встроенный конфиг пакета (для тестов / CI)
        return __DIR__ . '/../config/feature_flags_rules.php';
    }

    private function resolveDriver(): string
    {
        if ($this->testDriver !== null) {
            return $this->testDriver;
        }
        $config = $this->app->make('config');
        $driver = null;
        if (is_array($config) && isset($config['feature_flags.driver'])) {
            $driver = $config['feature_flags.driver'];
        } elseif (is_object($config) && method_exists($config, 'get')) {
            $driver = $config->get('feature_flags.driver');
        }
        return (string) ($driver ?: 'config');
    }

    private function createConfigFlagRepository(): ConfigFileFlagRepository
    {
        return new ConfigFileFlagRepository($this->resolveConfigPath(), $this->getSpecifications());
    }

    private function createEloquentFlagRepository(): EloquentFlagRepository
    {
        $model = new EloquentFeatureFlag();
        $model->setTable($this->getTablePrefix() . 'feature_flags');
        return new EloquentFlagRepository($model, $this->getSpecifications());
    }

    private function resolveConfigPath(): string
    {
        if ($this->testFlagsConfig !== null) {
            $tempFile = sys_get_temp_dir() . '/feature_flags_test_' . md5(serialize($this->testFlagsConfig)) . '.php';
            if (!file_exists($tempFile)) {
                file_put_contents($tempFile, "<?php return " . var_export($this->testFlagsConfig, true) . ";");
            }
            return $tempFile;
        }
        $config = $this->app->make('config');
        $path = $config instanceof ConfigContract ? $config->get('feature_flags.config_path') : null;
        $defaultPath = __DIR__ . '/../config/feature_flags_rules.php';
        return $path && file_exists($path) ? $path : $defaultPath;
    }

    private function getTablePrefix(): string
    {
        $config = $this->app->make('config');
        if ($config instanceof ConfigContract) {
            return $config->get('table_prefix', '');
        }
        return function_exists('evo') ? (evo()->config['table_prefix'] ?? '') : '';
    }

    private function getSpecifications(): array
    {
        $atomicSpecs = [
            new UserRoleSpecification(),
            new CategorySpecification(),
            new PercentageSpecification(),
            new DateBetweenSpecification(),
            new TargetIdSpecification(),
        ];

        return [
            new CompositeSpecification($atomicSpecs),
            ...$atomicSpecs,
        ];
    }
}
