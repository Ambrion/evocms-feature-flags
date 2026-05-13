<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Domain\ValueObject;

use DateTimeImmutable;

final readonly class FlagStatisticsFilter
{
    public function __construct(
        public ?string            $flagName = null,
        public ?DateTimeImmutable $from = null,
        public ?DateTimeImmutable $to = null,
        public ?string            $variant = null,
        public ?string            $ip = null,
        public int                $limit = 100,
        public int                $offset = 0,
    )
    {
    }

    public static function byFlagName(string $flagName): self
    {
        return new self(flagName: $flagName);
    }

    public function withDateRange(DateTimeImmutable $from, DateTimeImmutable $to): self
    {
        return new self(
            flagName: $this->flagName, from: $from, to: $to,
            variant: $this->variant, ip: $this->ip,
            limit: $this->limit, offset: $this->offset,
        );
    }

    public function withVariant(string $variant): self
    {
        return new self(
            flagName: $this->flagName, from: $this->from, to: $this->to,
            variant: $variant, ip: $this->ip,
            limit: $this->limit, offset: $this->offset,
        );
    }

    public function withPagination(int $limit, int $offset): self
    {
        return new self(
            flagName: $this->flagName, from: $this->from, to: $this->to,
            variant: $this->variant, ip: $this->ip,
            limit: max(1, $limit), offset: max(0, $offset),
        );
    }
}
