<?php declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Infrastructure\Repository;

use DateTimeImmutable;
use EvolutionCMS\FeatureFlags\Domain\Repository\FlagStatisticsRepositoryInterface;
use EvolutionCMS\FeatureFlags\Domain\ValueObject\FlagStatisticsFilter;
use EvolutionCMS\FeatureFlags\Domain\ValueObject\FlagStatisticsRecord;
use EvolutionCMS\FeatureFlags\Infrastructure\Model\EloquentFlagStatistics;
use EvolutionCMS\FeatureFlags\Tests\Infrastructure\Repository\FakeQueryBuilder;
use Illuminate\Database\Eloquent\Builder;

final readonly class EloquentFlagStatisticsRepository implements FlagStatisticsRepositoryInterface
{
    public function __construct(private EloquentFlagStatistics $model)
    {
    }

    public function save(FlagStatisticsRecord $record): void
    {
        $this->model->newQuery()->create([
            'flag_name' => $record->flagName,
            'result' => $record->result,
            'variant' => $record->variant,
            'weight' => $record->weight,
            'matched_rule' => $record->matchedRule,
            'context_hash' => $record->contextHash,
            'context' => $record->context,
            'ip' => $record->ip,
            'evaluated_at' => $record->evaluatedAt,
        ]);
    }

    public function findByFilter(FlagStatisticsFilter $filter): array
    {
        return $this->applyFiltersToQuery($this->model->newQuery(), $filter)
            ->orderBy('evaluated_at', 'desc')
            ->offset($filter->offset)
            ->limit($filter->limit)
            ->get()
            ->map(fn($m) => $this->mapToRecord($m))
            ->values()
            ->all();
    }

    public function countByFilter(FlagStatisticsFilter $filter): int
    {
        return $this->applyFiltersToQuery($this->model->newQuery(), $filter)->count();
    }

    public function getVariantDistribution(string $flagName, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        $results = $this->model->newQuery()
            ->where('flag_name', $flagName)
            ->whereBetween('evaluated_at', [$from, $to->setTime(23, 59, 59)])
            ->whereNotNull('variant')
            ->selectRaw('variant, COUNT(*) as count')
            ->groupBy('variant')
            ->get();

        $total = $results->sum('count');

        return $results->mapWithKeys(function ($row) use ($total) {
            return [
                $row->variant => [
                    'count' => (int)$row->count,
                    'percentage' => $total > 0 ? round(($row->count / $total) * 100, 1) : 0.0,
                ]
            ];
        })->toArray();
    }

    public function getTotalEvaluations(string $flagName, ?DateTimeImmutable $from = null): int
    {
        $query = $this->model->newQuery()->where('flag_name', $flagName);
        if ($from !== null) {
            $query->where('evaluated_at', '>=', $from);
        }

        return $query->count();
    }

    /**
     * Применяет условия фильтрации к запросу.
     * Выносится в отдельный метод для устранения дублирования кода.
     *
     * @template T of Builder|FakeQueryBuilder
     * @param T $query
     * @return T
     */
    private function applyFiltersToQuery(Builder|FakeQueryBuilder $query, FlagStatisticsFilter $filter)
    {
        if ($filter->flagName !== null) {
            $query->where('flag_name', $filter->flagName);
        }

        if ($filter->from !== null && $filter->to !== null) {
            // Включаем весь последний день (до 23:59:59)
            $toEndOfDay = $filter->to->setTime(23, 59, 59);
            $query->whereBetween('evaluated_at', [$filter->from, $toEndOfDay]);
        }

        if ($filter->variant !== null && trim($filter->variant) !== '') {
            $query->where('variant', trim($filter->variant));
        }

        if ($filter->ip !== null && trim($filter->ip) !== '') {
            $query->where('ip', trim($filter->ip));
        }

        return $query;
    }

    private function mapToRecord(EloquentFlagStatistics $model): FlagStatisticsRecord
    {
        $evaluatedAt = $model->evaluated_at instanceof DateTimeImmutable
            ? $model->evaluated_at
            : ($model->evaluated_at ? $model->evaluated_at->toDateTimeImmutable() : new DateTimeImmutable());

        return new FlagStatisticsRecord(
            flagName: $model->flag_name,
            result: $model->result,
            contextHash: $model->context_hash,
            ip: $model->ip,
            evaluatedAt: $evaluatedAt,
            variant: $model->variant,
            weight: $model->weight,
            matchedRule: $model->matched_rule,
            context: $model->context,
        );
    }

    /**
     * Группирует записи по context_hash и считает количество
     */
    public function getContextHashCounts(string $flagName, DateTimeImmutable $from, DateTimeImmutable $to): array
    {
        $toEndOfDay = $to->setTime(23, 59, 59);

        return $this->model->newQuery()
            ->where('flag_name', $flagName)
            ->whereBetween('evaluated_at', [$from, $toEndOfDay])
            ->selectRaw('context_hash, COUNT(*) as count')
            ->groupBy('context_hash')
            ->pluck('count', 'context_hash')
            ->toArray();
    }
}
