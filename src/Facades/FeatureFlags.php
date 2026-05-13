<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Facades;

use FeatureFlags\Core\Application\Service\FeatureFlagService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool isEnabled(string $flagName, array $context = [])
 * @method static ?string getVariant(string $flagName, array $context = [])
 *
 * Фасад для удобного вызова фич-флагов в сниппетах и плагинах Evo CMS.
 * Делегирует вызовы в FeatureFlagService через DI-контейнер.
 */
class FeatureFlags extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FeatureFlagService::class;
    }
}
