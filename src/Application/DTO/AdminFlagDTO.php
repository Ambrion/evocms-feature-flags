<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Application\DTO;

final readonly class AdminFlagDTO
{
    public function __construct(
        public string  $name,
        public mixed   $default_value = false,
        public ?array  $rules = null,
        public bool    $is_active = true,
        public ?string $description = null,
        public bool    $log_statistics = false,
    )
    {
    }

    /**
     * @throws \JsonException
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            default_value: self::parseDefaultValue($data['default_value'] ?? false),
            rules: isset($data['rules']) && is_string($data['rules'])
                ? json_decode($data['rules'], true, 512, JSON_THROW_ON_ERROR)
                : ($data['rules'] ?? null),
            is_active: (bool)($data['is_active'] ?? true),
            description: $data['description'] ?? null,
            log_statistics: (bool)($data['log_statistics'] ?? false),
        );
    }

    /**
     * Парсит значение из формы: "true" → true, "A" → "A", "123" → 123
     */
    private static function parseDefaultValue(mixed $value): mixed
    {
        if (is_bool($value) || is_int($value) || is_float($value) || $value === null) {
            return $value;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            $lower = strtolower($trimmed);

            if ($lower === 'true') {
                return true;
            }
            if ($lower === 'false') {
                return false;
            }
            if ($trimmed === '' || $lower === 'null') {
                return null;
            }
            if (preg_match('/^-?\d+$/', $trimmed)) {
                return (int)$trimmed;
            }
            if (preg_match('/^-?\d+\.\d+$/', $trimmed)) {
                return (float)$trimmed;
            }

            // Всё остальное — строка (вариант теста: "A", "B", "variant-1")
            return trim($trimmed, '"\'');
        }

        return $value;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'default_value' => $this->default_value,
            'rules' => $this->rules,
            'is_active' => $this->is_active,
            'description' => $this->description,
            'log_statistics' => $this->log_statistics,
        ];
    }
}
