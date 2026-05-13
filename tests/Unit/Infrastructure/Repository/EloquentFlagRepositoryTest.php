<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Tests\Unit\Infrastructure\Repository;


use EvolutionCMS\FeatureFlags\Infrastructure\Database\Models\EloquentFeatureFlag;
use EvolutionCMS\FeatureFlags\Infrastructure\Repository\EloquentFlagRepository;
use FeatureFlags\Core\Domain\Entity\FeatureFlag as DomainFeatureFlag;
use FeatureFlags\Core\Domain\Specification\CategorySpecification;
use FeatureFlags\Core\Domain\ValueObject\FlagName;
use Illuminate\Database\Eloquent\Builder;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Тестируем Eloquent-адаптер через моки.
 * Не подключаем реальную БД — проверяем только маппинг и контракт.
 */
final class EloquentFlagRepositoryTest extends TestCase
{
    #[Test]
    public function it_maps_eloquent_model_to_domain_entity(): void
    {
        // ARRANGE: Мокаем Eloquent-модель и билдер
        $builderMock = $this->createMock(Builder::class);
        $builderMock->expects($this->once())
            ->method('where')
            ->with('name', 'test_flag')
            ->willReturnSelf();
        $builderMock->expects($this->once())
            ->method('first')
            ->willReturn(new class extends EloquentFeatureFlag {
                public string $name = 'test_flag';
                public bool $default_value = true;
                public string $rules = '[{"condition":"category=electronics","value":false}]';
                public bool $is_active = true;

                // Заглушки для Eloquent
                public function getTable(): string
                {
                    return 'feature_flags';
                }

                public function getConnectionName(): ?string
                {
                    return null;
                }
            });

        $modelMock = $this->getMockBuilder(EloquentFeatureFlag::class)
            ->onlyMethods(['newQuery'])
            ->getMock();
        $modelMock->expects($this->once())
            ->method('newQuery')
            ->willReturn($builderMock);

        $specifications = [new CategorySpecification()];
        $repository = new EloquentFlagRepository($modelMock, $specifications);

        // ACT
        $flag = $repository->findByName(new FlagName('test_flag'));

        // ASSERT
        $this->assertInstanceOf(DomainFeatureFlag::class, $flag);
        $this->assertSame('test_flag', $flag->name->value);
        $this->assertTrue($flag->default);
        $this->assertCount(1, $flag->rules);
        $this->assertSame('category=electronics', $flag->rules[0]['condition']);
    }

    #[Test]
    public function it_returns_null_for_inactive_flag(): void
    {
        $builderMock = $this->createMock(Builder::class);
        $builderMock->expects($this->once())
            ->method('where')
            ->with('name', 'deprecated_flag')
            ->willReturnSelf();
        $builderMock->expects($this->once())
            ->method('first')
            ->willReturn(new class extends EloquentFeatureFlag {
                public string $name = 'deprecated_flag';
                public bool $default_value = false;
                public string $rules = '[]';
                public bool $is_active = false; // Деактивирован
            });

        $modelMock = $this->getMockBuilder(EloquentFeatureFlag::class)
            ->onlyMethods(['newQuery'])
            ->getMock();
        $modelMock->expects($this->once())
            ->method('newQuery')
            ->willReturn($builderMock);

        $repository = new EloquentFlagRepository($modelMock, []);
        $flag = $repository->findByName(new FlagName('deprecated_flag'));

        $this->assertNull($flag);
    }

    #[Test]
    public function it_returns_null_when_flag_not_found(): void
    {
        $builderMock = $this->createMock(Builder::class);
        $builderMock->expects($this->once())
            ->method('where')
            ->with('name', 'nonexistent')
            ->willReturnSelf();
        $builderMock->expects($this->once())
            ->method('first')
            ->willReturn(null);

        $modelMock = $this->getMockBuilder(EloquentFeatureFlag::class)
            ->onlyMethods(['newQuery'])
            ->getMock();
        $modelMock->expects($this->once())
            ->method('newQuery')
            ->willReturn($builderMock);

        $repository = new EloquentFlagRepository($modelMock, []);
        $flag = $repository->findByName(new FlagName('nonexistent'));

        $this->assertNull($flag);
    }
}
