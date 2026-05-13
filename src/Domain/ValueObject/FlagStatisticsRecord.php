<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Domain\ValueObject;

use DateTimeImmutable;
use InvalidArgumentException;
use JsonException;
use JsonSerializable;
use SensitiveParameter;

final readonly class FlagStatisticsRecord implements JsonSerializable
{
    public function __construct(
        public string                       $flagName,
        public bool                         $result,
        public string                       $contextHash,
        #[SensitiveParameter] public string $ip,
        public DateTimeImmutable            $evaluatedAt,
        public ?string                      $variant = null,
        public ?float                       $weight = null,
        public ?string                      $matchedRule = null,
        public ?array                       $context = null,
    )
    {
        if ($this->flagName === '') {
            throw new InvalidArgumentException('Flag name cannot be empty.');
        }
    }

    /**
     * @throws JsonException
     */
    public static function fromEvaluation(
        string             $flagName,
        bool               $result,
        array              $context = [],
        string             $ip = '',
        ?DateTimeImmutable $evaluatedAt = null,
        ?string            $variant = null,
        ?float             $weight = null,
        ?string            $matchedRule = null,
    ): self
    {
        if ($ip === '') {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
        }
        if ($evaluatedAt === null) {
            $evaluatedAt = new DateTimeImmutable();
        }

        return new self(
            flagName: $flagName,
            result: $result,
            contextHash: md5(json_encode($context, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)),
            ip: $ip,
            evaluatedAt: $evaluatedAt,
            variant: $variant,
            weight: $weight,
            matchedRule: $matchedRule,
            context: $context,
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'flagName' => $this->flagName,
            'result' => $this->result,
            'contextHash' => $this->contextHash,
            'ip' => $this->ip,
            'evaluatedAt' => $this->evaluatedAt->format('Y-m-d H:i:s'),
            'variant' => $this->variant,
            'weight' => $this->weight,
            'matchedRule' => $this->matchedRule,
            'context' => $this->context,
        ];
    }

    /** @throws JsonException */
    public function __toString(): string
    {
        return json_encode($this->jsonSerialize(), JSON_THROW_ON_ERROR);
    }
}
