<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Infrastructure\Repository;

use EvolutionCMS\FeatureFlags\Application\DTO\AdminFlagDTO;
use EvolutionCMS\FeatureFlags\Domain\Repository\FlagAdminRepositoryInterface;
use EvolutionCMS\FeatureFlags\Infrastructure\Database\Models\EloquentFeatureFlag;

final readonly class FlagAdminEloquentRepository implements FlagAdminRepositoryInterface
{
    public function __construct(
        private EloquentFeatureFlag $model = new EloquentFeatureFlag()
    )
    {
    }

    public function list(): array
    {
        $models = $this->model->orderBy('name')->get();

        $result = [];
        foreach ($models as $m) {
            $result[$m->name] = $this->toDto($m);
        }

        return $result;
    }

    public function findByName(string $name): ?AdminFlagDTO
    {
        $model = $this->model->where('name', $name)->first();

        return $model ? $this->toDto($model) : null;
    }

    public function isWritable(): bool
    {
        // В будущем можно проверять config('feature_flags.read_only_mode')
        return true;
    }

    public function create(AdminFlagDTO $dto): AdminFlagDTO
    {
        if ($this->model->where('name', $dto->name)->exists()) {
            throw new \DomainException("Flag '{$dto->name}' already exists.");
        }

        $model = $this->model->create($this->toModelData($dto));

        return $this->toDto($model);
    }

    public function update(string $name, AdminFlagDTO $dto): AdminFlagDTO
    {
        $model = $this->model->where('name', $name)->firstOrFail();
        $model->update($this->toModelData($dto));

        return $this->toDto($model);
    }

    public function delete(string $name): void
    {
        $this->model->where('name', $name)->delete();
    }

    private function toDto(EloquentFeatureFlag $model): AdminFlagDTO
    {
        return new AdminFlagDTO(
            name: $model->name,
            default_value: $model->default_value,
            rules: $model->rules,
            is_active: $model->is_active,
            description: $model->description,
            log_statistics: $model->log_statistics,
        );
    }

    private function toModelData(AdminFlagDTO $dto): array
    {
        return [
            'name' => $dto->name,
            'default_value' => $dto->default_value,
            'rules' => $dto->rules,
            'is_active' => $dto->is_active,
            'description' => $dto->description,
            'log_statistics' => $dto->log_statistics,
        ];
    }

    public function isLoggingEnabled(string $name): bool
    {
        $flag = $this->model->newQuery()
            ->where('name', $name)
            ->first();

        return $flag !== null && $flag->log_statistics;
    }
}
