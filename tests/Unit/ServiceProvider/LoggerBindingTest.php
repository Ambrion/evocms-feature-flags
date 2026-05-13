<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Tests\Unit\ServiceProvider;

use EvolutionCMS\FeatureFlags\FeatureFlagsServiceProvider;
use FeatureFlags\Core\Application\Service\FeatureFlagService;
use FeatureFlags\Core\Domain\Logger\FlagUsageLoggerInterface;
use FeatureFlags\Core\Domain\Logger\NullFlagUsageLogger;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;

final class LoggerBindingTest extends TestCase
{
    #[Test]
    public function it_defaults_to_null_logger_when_logging_is_disabled(): void
    {
        $container = $this->createContainer(['feature_flags.log_to_db' => false]);
        $provider = new FeatureFlagsServiceProvider($container);
        $provider->register();

        $logger = $container->make(FlagUsageLoggerInterface::class);
        $this->assertInstanceOf(NullFlagUsageLogger::class, $logger);
    }

    #[Test]
    public function it_injects_logger_into_feature_flag_service(): void
    {
        $container = $this->createContainer(['feature_flags.log_to_db' => false]);
        $provider = new FeatureFlagsServiceProvider($container);
        $provider->register();

        $service = $container->make(FeatureFlagService::class);

        // Проверяем, что сервис получил логгер из контейнера
        $reflection = new ReflectionProperty($service, 'logger');
        $logger = $reflection->getValue($service);

        $this->assertInstanceOf(FlagUsageLoggerInterface::class, $logger);
    }

    private function createContainer(array $configValues): Container
    {
        $container = new Container();
        $config = new ConfigRepository([
            'table_prefix' => '',
            'feature_flags.config_path' => dirname(__DIR__) . '/../../config/feature_flags_rules.php',
            'feature_flags.driver' => 'config',
            'feature_flags.log_to_db' => false,
        ]);
        foreach ($configValues as $key => $value) {
            $config->set($key, $value);
        }
        $container->instance('config', $config);

        return $container;
    }
}
