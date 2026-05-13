<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Application\DTO;

use DateTimeImmutable;
use EvolutionCMS\FeatureFlags\Domain\ValueObject\FlagStatisticsRecord;
use JsonSerializable;

final readonly class FlagStatisticsSummary implements JsonSerializable
{
    public function __construct(
        public string $flagName,
        public DateTimeImmutable $periodFrom,
        public DateTimeImmutable $periodTo,
        public int $totalEvaluations,
        public array $variantDistribution,
        public array $recentRecords,
    ) {}

    /**
     * Сериализация для JSON-ответов (API)
     */
    public function jsonSerialize(): array
    {
        return [
            'flagName' => $this->flagName,
            'period' => [
                'from' => $this->periodFrom->format('Y-m-d H:i:s'),
                'to' => $this->periodTo->format('Y-m-d H:i:s'),
            ],
            'totalEvaluations' => $this->totalEvaluations,
            'variantDistribution' => $this->variantDistribution,
            'recentRecords' => array_map(
                fn($r) => $r instanceof FlagStatisticsRecord ? $r->jsonSerialize() : $r,
                $this->recentRecords
            ),
        ];
    }

    /**
     * Удобный алиас для jsonSerialize()
     */
    public function toJson(): string
    {
        return json_encode($this->jsonSerialize(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Для отладки в Blade: {{ $summary }}
     */
    public function __toString(): string
    {
        return $this->toJson();
    }
}
