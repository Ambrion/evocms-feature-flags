<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Domain\Repository;

use DateTimeImmutable;
use EvolutionCMS\FeatureFlags\Domain\ValueObject\FlagStatisticsFilter;
use EvolutionCMS\FeatureFlags\Domain\ValueObject\FlagStatisticsRecord;

/**
 * Контракт для сохранения статистики использования флагов.
 * Реализация может быть: БД, файл, очередь, внешний сервис.
 */
interface FlagStatisticsRepositoryInterface
{
    public function save(FlagStatisticsRecord $record): void;

    public function findByFilter(FlagStatisticsFilter $filter): array;

    public function getVariantDistribution(string $flagName, DateTimeImmutable $from, DateTimeImmutable $to): array;

    public function getTotalEvaluations(string $flagName, ?DateTimeImmutable $from = null): int;

    public function countByFilter(FlagStatisticsFilter $filter): int;
}
