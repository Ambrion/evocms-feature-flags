<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Tests\Unit\Infrastructure\Repository;

use EvolutionCMS\FeatureFlags\Infrastructure\Repository\DbFlagRepository;
use FeatureFlags\Core\Domain\Entity\FeatureFlag;
use FeatureFlags\Core\Domain\Specification\CategorySpecification;
use FeatureFlags\Core\Domain\Specification\TargetIdSpecification;
use FeatureFlags\Core\Domain\ValueObject\FlagName;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class DbFlagRepositoryTest extends TestCase
{
    #[Test]
    public function it_maps_database_row_to_feature_flag_entity(): void
    {
        // Arrange: Имитируем строку из БД
        $dbRow = [
            'name' => 'show_premium_badge',
            'default_value' => 0,
            'rules' => json_encode([
                ['condition' => 'category=electronics', 'value' => true],
                ['condition' => 'target_id IN (101,102)', 'value' => true],
            ]),
            'is_active' => 1,
        ];

        // Мокаем "запрос к БД" (в реальности здесь будет PDO или $evo->db)
        $fetchCallback = fn(string $flagName) => $flagName === 'show_premium_badge' ? $dbRow : null;

        // Спецификации (те же, что и в конфиг-репозитории)
        $specifications = [
            new CategorySpecification(),
            new TargetIdSpecification(),
        ];

        $repository = new DbFlagRepository($fetchCallback, $specifications);

        // Act
        $flag = $repository->findByName(new FlagName('show_premium_badge'));

        // Assert
        $this->assertInstanceOf(FeatureFlag::class, $flag);
        $this->assertEquals('show_premium_badge', $flag->name->value);
        $this->assertFalse($flag->default);
        $this->assertCount(2, $flag->rules);
        $this->assertSame('category=electronics', $flag->rules[0]['condition']);
        $this->assertTrue($flag->rules[0]['value']);
    }

    #[Test]
    public function it_returns_null_for_inactive_flag(): void
    {
        $dbRow = [
            'name' => 'deprecated_feature',
            'default_value' => 1,
            'rules' => json_encode([]),
            'is_active' => 0, // Флаг деактивирован
        ];

        $fetchCallback = fn(string $flagName) => $flagName === 'deprecated_feature' ? $dbRow : null;
        $specifications = [];

        $repository = new DbFlagRepository($fetchCallback, $specifications);

        $flag = $repository->findByName(new FlagName('deprecated_feature'));

        // Деактивированный флаг должен возвращаться как "не найден"
        $this->assertNull($flag);
    }

    #[Test]
    public function it_returns_null_when_flag_not_found_in_db(): void
    {
        $fetchCallback = fn(string $flagName) => null;
        $specifications = [];

        $repository = new DbFlagRepository($fetchCallback, $specifications);

        $flag = $repository->findByName(new FlagName('nonexistent_flag'));

        $this->assertNull($flag);
    }
}
