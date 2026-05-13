<?php
declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Tests\Integration;

use EvolutionCMS\FeatureFlags\FeatureFlagsServiceProvider;
use FeatureFlags\Core\Application\Service\FeatureFlagService;
use FeatureFlags\Core\Domain\Repository\FlagRepositoryInterface;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;

final class ServiceProviderRegistrationTest extends TestCase
{
    #[Test]
    public function it_registers_flag_repository_in_container(): void
    {
        // ✅ Используем стандартный контейнер Laravel/Evo
        // Он реализует Application, легковесный и не требует сложных моков
        $container = new Container();

        // Настраиваем конфигурацию (провайдер читает её через make('config'))
        $container->bind('config', fn() => [
            'table_prefix' => '',
            'feature_flags.config_path' => null,
        ]);

        // Тестовые флаги
        $flagsConfig = [
            'test_flag' => ['default' => true, 'rules' => []]
        ];

        // Регистрируем провайдер
        $provider = new FeatureFlagsServiceProvider($container);
        $provider->setConfig($flagsConfig);
        $provider->register();

        // Assert 1: интерфейс привязан
        $this->assertTrue($container->bound(FlagRepositoryInterface::class));

        // Assert 2: сервис резолвится
        $service = $container->make(FeatureFlagService::class);
        $this->assertInstanceOf(FeatureFlagService::class, $service);

        // Assert 3: репозиторий реализует контракт (DIP)
        $reflection = new ReflectionProperty($service, 'repository');
        $repository = $reflection->getValue($service);
        $this->assertInstanceOf(FlagRepositoryInterface::class, $repository);
    }
}
