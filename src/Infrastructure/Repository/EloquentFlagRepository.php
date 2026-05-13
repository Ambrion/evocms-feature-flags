<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Infrastructure\Repository;


use EvolutionCMS\FeatureFlags\Infrastructure\Database\Models\EloquentFeatureFlag;
use FeatureFlags\Core\Domain\Entity\FeatureFlag as DomainFeatureFlag;
use FeatureFlags\Core\Domain\Repository\FlagRepositoryInterface;
use FeatureFlags\Core\Domain\Specification\ConditionSpecificationInterface;
use FeatureFlags\Core\Domain\ValueObject\FlagName;

/**
 * Инфраструктурный адаптер: читает флаги через Eloquent.
 * Маппит Eloquent-модель в доменную сущность.
 * Не зависит от конкретной БД — только от контракта Eloquent.
 */
final readonly class EloquentFlagRepository implements FlagRepositoryInterface
{
    /**
     * @param ConditionSpecificationInterface[] $specifications
     */
    public function __construct(
        private EloquentFeatureFlag $model,
        private array $specifications = []
    ) {}

    public function findByName(FlagName $flagName): ?DomainFeatureFlag
    {
        // 1. Ищем запись через Eloquent
        $eloquentFlag = $this->model
            ->newQuery()
            ->where('name', $flagName->value)
            ->first();

        // 2. Не найдено или деактивировано -> возвращаем null
        if ($eloquentFlag === null || !$eloquentFlag->is_active) {
            return null;
        }

        // 3. Маппим в доменную сущность
        $rules = is_array($eloquentFlag->rules)
            ? $eloquentFlag->rules
            : (is_string($eloquentFlag->rules) ? json_decode($eloquentFlag->rules, true) ?: [] : []);

        return new DomainFeatureFlag(
            name: $flagName,
            default: $eloquentFlag->default_value,
            rules: $rules,
            specifications: $this->specifications
        );
    }
}
