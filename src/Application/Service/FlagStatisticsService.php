<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Application\Service;

use DateMalformedStringException;
use DateTimeImmutable;
use EvolutionCMS\FeatureFlags\Application\DTO\FlagStatisticsSummary;
use EvolutionCMS\FeatureFlags\Domain\Repository\FlagStatisticsRepositoryInterface;
use EvolutionCMS\FeatureFlags\Domain\ValueObject\FlagStatisticsFilter;
use Exception;

final readonly class FlagStatisticsService
{
    public function __construct(private FlagStatisticsRepositoryInterface $repository)
    {
    }

    /**
     * @param string $flagName
     * @param DateTimeImmutable|null $from
     * @param DateTimeImmutable|null $to
     * @return FlagStatisticsSummary
     * @throws DateMalformedStringException
     */
    public function getFlagSummary(
        string $flagName,
        ?DateTimeImmutable $from = null,
        ?DateTimeImmutable $to = null
    ): FlagStatisticsSummary {
        $from = $from ?? (new DateTimeImmutable())->modify('-7 days');
        $to   = $to ?? new DateTimeImmutable();

        $baseFilter = FlagStatisticsFilter::byFlagName($flagName)
            ->withDateRange($from, $to);

        return new FlagStatisticsSummary(
            flagName: $flagName,
            periodFrom: $from,
            periodTo: $to,
            totalEvaluations: $this->repository->countByFilter($baseFilter),
            variantDistribution: $this->repository->getVariantDistribution($flagName, $from, $to),
            recentRecords: $this->repository->findByFilter(
                $baseFilter->withPagination(10, 0)
            ),
        );
    }

    /**
     * @param string $flagName
     * @param array $filters
     * @param int $page
     * @param int $perPage
     * @return array
     * @throws Exception
     */
    public function searchRecords(
        string $flagName,
        array $filters = [],
        int $page = 1,
        int $perPage = 50
    ): array {
        $from = $filters['from'] ?? null;
        $from = $from instanceof DateTimeImmutable
            ? $from
            : (is_string($from) ? new DateTimeImmutable($from) : (new DateTimeImmutable())->modify('-30 days'));

        $to = $filters['to'] ?? null;
        $to = $to instanceof DateTimeImmutable
            ? $to
            : (is_string($to) ? new DateTimeImmutable($to) : new DateTimeImmutable());

        $filter = FlagStatisticsFilter::byFlagName($flagName)
            ->withDateRange($from, $to);

        if (!empty($filters['variant']) && trim((string) $filters['variant']) !== '') {
            $filter = $filter->withVariant(trim((string) $filters['variant']));
        }

        $total = $this->repository->countByFilter($filter);
        $totalPages = $total > 0 ? (int) ceil($total / $perPage) : 1;
        $currentPage = max(1, min($page, $totalPages));

        $records = $this->repository->findByFilter(
            $filter->withPagination($perPage, ($currentPage - 1) * $perPage)
        );

        return [
            'records' => $records,
            'pagination' => [
                'current_page' => $currentPage,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_prev' => $currentPage > 1,
                'has_next' => $currentPage < $totalPages,
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ],
        ];
    }

    /**
     * Возвращает количество оценок для каждого contextHash в заданных фильтрах
     * @return array<string, int> [hash => count]
     * @throws DateMalformedStringException
     */
    public function getContextHashCounts(string $flagName, array $filters): array
    {
        $from = $filters['from'] ?? (new DateTimeImmutable())->modify('-30 days');
        $to = $filters['to'] ?? new DateTimeImmutable();
        $from = $from instanceof DateTimeImmutable ? $from : new DateTimeImmutable($from);
        $to = $to instanceof DateTimeImmutable ? $to : new DateTimeImmutable($to);

        return $this->repository->getContextHashCounts($flagName, $from, $to);
    }
}
