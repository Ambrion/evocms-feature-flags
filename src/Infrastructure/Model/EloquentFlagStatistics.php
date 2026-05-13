<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Infrastructure\Model;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $flag_name
 * @property bool $result
 * @property string|null $variant
 * @property float|null $weight
 * @property string|null $matched_rule
 * @property string $context_hash
 * @property array|null $context
 * @property string $ip
 * @property Carbon $evaluated_at
 */
class EloquentFlagStatistics extends Model
{
    protected $table = 'feature_flag_statistics';
    public $timestamps = false;

    protected $fillable = [
        'flag_name',
        'result',
        'variant',
        'weight',
        'matched_rule',
        'context',
        'context_hash',
        'ip',
        'evaluated_at',
    ];

    protected $casts = [
        'result' => 'boolean',
        'weight' => 'float',
        'context' => 'array',
        'evaluated_at' => 'datetime',
    ];

    public function scopeByFlagName($query, string $flagName)
    {
        return $query->where('flag_name', $flagName);
    }

    public function scopeBetweenDates($query, DateTimeInterface $from, DateTimeInterface $to)
    {
        return $query->whereBetween('evaluated_at', [$from, $to]);
    }
}
