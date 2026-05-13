<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Tests\Domain\ValueObject;

use EvolutionCMS\FeatureFlags\Domain\ValueObject\FlagStatisticsFilter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FlagStatisticsFilterTest extends TestCase
{
    #[Test]
    public function filter_by_flag_name_creates_correct_instance(): void
    {
        $filter = FlagStatisticsFilter::byFlagName('promo_test');

        $this->assertSame('promo_test', $filter->flagName);
        $this->assertNull($filter->variant);
        $this->assertNull($filter->from);
        $this->assertNull($filter->to);
        $this->assertSame(100, $filter->limit);
    }

    #[Test]
    public function with_date_range_preserves_existing_values_and_adds_dates(): void
    {
        $base = FlagStatisticsFilter::byFlagName('ab_test');
        $from = new \DateTimeImmutable('2026-01-01');
        $to   = new \DateTimeImmutable('2026-01-31');

        $filtered = $base->withDateRange($from, $to);

        $this->assertSame('ab_test', $filtered->flagName);
        $this->assertSame($from, $filtered->from);
        $this->assertSame($to, $filtered->to);
    }

    #[Test]
    public function with_variant_adds_filter_criteria(): void
    {
        $filter = FlagStatisticsFilter::byFlagName('header_test')->withVariant('B');

        $this->assertSame('header_test', $filter->flagName);
        $this->assertSame('B', $filter->variant);
    }
}
