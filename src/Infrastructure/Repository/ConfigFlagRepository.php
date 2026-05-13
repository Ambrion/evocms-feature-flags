<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Infrastructure\Repository;

use FeatureFlags\Core\Domain\Entity\FeatureFlag;
use FeatureFlags\Core\Domain\Repository\FlagRepositoryInterface;
use FeatureFlags\Core\Domain\Specification\ConditionSpecificationInterface;
use FeatureFlags\Core\Domain\ValueObject\FlagName;

/**
 * Инфраструктурный адаптер: читает конфигурацию флагов из PHP-массива.
 * Не знает про $modx, БД или кэш. Только array -> FeatureFlag.
 */
final readonly class ConfigFlagRepository implements FlagRepositoryInterface
{
    /**
     * @param array<string, array{default?: bool, rules: array}> $flagsConfig
     * @param ConditionSpecificationInterface[] $specifications
     */
    public function __construct(
        private array $flagsConfig,
        private array $specifications = []
    )
    {
    }

    public function findByName(FlagName $flagName): ?FeatureFlag
    {
        $key = $flagName->value;

        if (!isset($this->flagsConfig[$key])) {
            return null;
        }

        $config = $this->flagsConfig[$key];

        // Теперь передаём спецификации в сущность — правила начнут работать!
        return new FeatureFlag(
            name: $flagName,
            default: (bool)($config['default'] ?? false),
            rules: $config['rules'] ?? [],
            specifications: $this->specifications
        );
    }
}
