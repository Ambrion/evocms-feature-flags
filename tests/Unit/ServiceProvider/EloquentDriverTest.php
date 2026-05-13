<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Tests\Unit\ServiceProvider;

use EvolutionCMS\FeatureFlags\FeatureFlagsServiceProvider;
use EvolutionCMS\FeatureFlags\Infrastructure\Repository\EloquentFlagRepository;
use FeatureFlags\Core\Domain\Repository\FlagRepositoryInterface;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class EloquentDriverTest extends TestCase
{
    #[Test]
    public function it_binds_eloquent_repository_when_driver_is_eloquent(): void
    {
        $container = $this->createContainer();
        $provider = new FeatureFlagsServiceProvider($container);

        // Явно задаём драйвер через setConfig (обходит quirks чтения конфига в тестах)
        $provider->setConfig([], 'eloquent');
        $provider->register();

        $repository = $container->make(FlagRepositoryInterface::class);

        $this->assertInstanceOf(EloquentFlagRepository::class, $repository);
    }

    private function createContainer(): Container
    {
        $container = new Container();
        $config = new ConfigRepository([
            'table_prefix' => '',
            'feature_flags.config_path' => dirname(__DIR__) . '/../../config/feature_flags_rules.php',
        ]);
        $container->instance('config', $config);
        return $container;
    }
}
