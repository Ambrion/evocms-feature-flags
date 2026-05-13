<?php
declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Tests\Unit\Facade;

use EvolutionCMS\FeatureFlags\Facades\FeatureFlags;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class FeatureFlagsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Инициализируем минимальный контейнер для работы фасада в изоляции
        $app = new Container();
        Facade::setFacadeApplication($app);
    }

    protected function tearDown(): void
    {
        // Очищаем состояние фасада и контейнера между тестами
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
    }

    #[Test]
    public function it_delegates_is_enabled_to_service(): void
    {
        $fakeService = new FakeFeatureFlagService();
        $fakeService->isEnabledResult = true;

        // Подменяем сервис в контейнере через фасад
        FeatureFlags::swap($fakeService);

        $result = FeatureFlags::isEnabled('new_product_template', ['target_id' => 42]);

        $this->assertTrue($result);
        $this->assertSame('new_product_template', $fakeService->lastArgs['flagName']);
        $this->assertSame(['target_id' => 42], $fakeService->lastArgs['context']);
    }

    #[Test]
    public function it_delegates_get_variant_to_service(): void
    {
        $fakeService = new FakeFeatureFlagService();
        $fakeService->variantResult = 'B';

        FeatureFlags::swap($fakeService);

        $result = FeatureFlags::getVariant('header_ab_test', ['user_hash' => 'abc123']);

        $this->assertSame('B', $result);
        $this->assertSame('header_ab_test', $fakeService->lastArgs['flagName']);
    }
}

/**
 * Тестовый дубль для проверки делегирования.
 * Не использует моки, поэтому не конфликтует с final readonly классами.
 */
class FakeFeatureFlagService
{
    public ?bool $isEnabledResult = null;
    public ?string $variantResult = null;
    public array $lastArgs = [];

    public function isEnabled(string $flagName, array $context = []): bool
    {
        $this->lastArgs = ['flagName' => $flagName, 'context' => $context];

        return $this->isEnabledResult ?? false;
    }

    public function getVariant(string $flagName, array $context = []): ?string
    {
        $this->lastArgs = ['flagName' => $flagName, 'context' => $context];

        return $this->variantResult;
    }
}
