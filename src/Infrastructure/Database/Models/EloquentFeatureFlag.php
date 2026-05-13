<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Infrastructure\Database\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent-модель для таблицы feature_flags.
 * Только инфраструктурный слой — не знает про домен.
 *
 * @property string $name
 * @property mixed $default_value
 * @property array|null $rules
 * @property bool $is_active
 * @property string|null $description
 * @property string $created_at
 * @property string $updated_at
 */
class EloquentFeatureFlag extends Model
{
    protected $table = 'feature_flags';
    protected $primaryKey = 'name';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'default_value' => 'json',
        'is_active' => 'boolean',
        'log_statistics' => 'boolean',
        'rules' => 'array',
    ];

    protected $fillable = [
        'name',
        'default_value',
        'rules',
        'is_active',
        'log_statistics',
        'description',
    ];

    public function getKey(): mixed
    {
        $key = parent::getKey();

        return $key === null ? '' : $key;
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    protected function setKeysForSaveOperation(): void
    {
        if (empty($this->getAttribute($this->getKeyName()))) {
            $this->setAttribute($this->getKeyName(), '');
        }
    }
}
