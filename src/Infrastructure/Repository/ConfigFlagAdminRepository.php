<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Infrastructure\Repository;

use EvolutionCMS\FeatureFlags\Application\DTO\AdminFlagDTO;
use EvolutionCMS\FeatureFlags\Domain\Repository\FlagAdminRepositoryInterface;
use RuntimeException;

final readonly class ConfigFlagAdminRepository implements FlagAdminRepositoryInterface
{
    public function __construct(private string $configPath) {}

    public function list(): array
    {
        $raw = file_exists($this->configPath) ? require $this->configPath : [];
        $result = [];

        foreach ($raw as $name => $data) {
            // Маппинг конфига → AdminFlagDTO
            $result[$name] = new AdminFlagDTO(
                name: $name,
                default_value: (bool)($data['default'] ?? false),
                rules: $data['rules'] ?? [],
                is_active: true, // Конфигурационные флаги всегда активны
                description: 'Загружено из конфигурационного файла',
            );
        }

        return $result;
    }

    public function findByName(string $name): ?AdminFlagDTO
    {
        return $this->list()[$name] ?? null;
    }

    public function isWritable(): bool
    {
        return false;
    }

    public function create(AdminFlagDTO $dto): AdminFlagDTO
    {
        throw new RuntimeException('Config provider is read-only. Use database repository for CRUD.');
    }

    public function update(string $name, AdminFlagDTO $dto): AdminFlagDTO
    {
        throw new RuntimeException('Config provider is read-only. Use database repository for CRUD.');
    }

    public function delete(string $name): void
    {
        throw new RuntimeException('Config provider is read-only. Use database repository for CRUD.');
    }

    public function isLoggingEnabled(string $name): bool
    {
        if (!file_exists($this->configPath)) {
            return false;
        }

        $config = require $this->configPath;
        $flagConfig = $config[$name] ?? null;

        if ($flagConfig === null) {
            return false;
        }

        return (bool) ($flagConfig['log_statistics'] ?? false);
    }
}
