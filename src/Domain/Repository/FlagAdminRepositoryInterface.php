<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Domain\Repository;

use EvolutionCMS\FeatureFlags\Application\DTO\AdminFlagDTO;

interface FlagAdminRepositoryInterface
{
    /** @return array<string, AdminFlagDTO> */
    public function list(): array;

    public function findByName(string $name): ?AdminFlagDTO;

    public function isWritable(): bool;

    public function create(AdminFlagDTO $dto): AdminFlagDTO;

    public function update(string $name, AdminFlagDTO $dto): AdminFlagDTO;

    public function delete(string $name): void;

    public function isLoggingEnabled(string $name): bool;
}
