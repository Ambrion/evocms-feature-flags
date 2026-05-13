<?php
declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Tests\Unit\ServiceProvider;

use EvolutionCMS\FeatureFlags\FeatureFlagsServiceProvider;
use EvolutionCMS\FeatureFlags\Infrastructure\Repository\ConfigFileFlagRepository;
use FeatureFlags\Core\Domain\Repository\FlagRepositoryInterface;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class DriverSwitchingTest extends TestCase
{
    #[Test]
    public function it_resolves_config_repository_when_driver_is_config(): void
    {
        $container = $this->createContainer(['feature_flags.driver' => 'config']);
        $provider = new FeatureFlagsServiceProvider($container);
        $provider->register();

        $repo = $container->make(FlagRepositoryInterface::class);
        $this->assertInstanceOf(ConfigFileFlagRepository::class, $repo);
    }

    #[Test]
    public function it_defaults_to_config_driver_when_not_specified(): void
    {
        $container = $this->createContainer(['feature_flags.driver' => null]);
        $provider = new FeatureFlagsServiceProvider($container);
        $provider->register();

        $repo = $container->make(FlagRepositoryInterface::class);
        $this->assertInstanceOf(ConfigFileFlagRepository::class, $repo);
    }

    private function createContainer(array $configValues): Container
    {
        $container = new Container();

        // Создаём реальный конфиг-репозиторий Laravel/Evo
        $config = new ConfigRepository([
            'table_prefix' => '',
            'feature_flags.config_path' => dirname(__DIR__) . '/../../config/feature_flags_rules.php',
        ]);
        foreach ($configValues as $key => $value) {
            $config->set($key, $value);
        }

        $container->instance('config', $config);
        return $container;
    }
}
