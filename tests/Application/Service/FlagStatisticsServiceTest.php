<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Tests\Application\Service;

use DateTimeImmutable;
use EvolutionCMS\FeatureFlags\Application\DTO\FlagStatisticsSummary;
use EvolutionCMS\FeatureFlags\Application\Service\FlagStatisticsService;
use EvolutionCMS\FeatureFlags\Domain\Repository\FlagStatisticsRepositoryInterface;
use EvolutionCMS\FeatureFlags\Domain\ValueObject\FlagStatisticsFilter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FlagStatisticsServiceTest extends TestCase
{
    #[Test]
    public function getFlagSummary_returns_dto_with_correct_distribution(): void
    {
        $repoMock = $this->createMock(FlagStatisticsRepositoryInterface::class);

        $repoMock->method('countByFilter')
            ->with($this->isInstanceOf(FlagStatisticsFilter::class))
            ->willReturn(150);

        $repoMock->method('getVariantDistribution')
            ->willReturn([
                'A' => ['count' => 50, 'percentage' => 33.3],
                'B' => ['count' => 60, 'percentage' => 40.0],
            ]);

        $repoMock->method('findByFilter')->willReturn([]);

        $service = new FlagStatisticsService($repoMock);

        // Передаём явные даты, чтобы проверить проброс параметров
        $from = new DateTimeImmutable('2026-05-01');
        $to   = new DateTimeImmutable('2026-05-31');

        $summary = $service->getFlagSummary('promo_test', $from, $to);

        // ASSERT
        $this->assertInstanceOf(FlagStatisticsSummary::class, $summary);
        $this->assertSame(150, $summary->totalEvaluations);
        $this->assertArrayHasKey('A', $summary->variantDistribution);
        $this->assertEquals(33.3, $summary->variantDistribution['A']['percentage']);

        // Дополнительно: проверяем, что период сохранился в DTO
        $this->assertEquals($from, $summary->periodFrom);
        $this->assertEquals($to, $summary->periodTo);
    }
}
