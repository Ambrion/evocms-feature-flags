<?php declare(strict_types=1);

use EvolutionCMS\FeatureFlags\Presentation\Http\Controllers\Admin\FlagAdminController;
use EvolutionCMS\FeatureFlags\Presentation\Http\Controllers\Admin\FlagStatisticsController;
use Illuminate\Support\Facades\Route;

/**
 * Роуты для модуля управления фич-флагами в админке Evolution CMS.
 *
 * Поддерживает:
 * - Традиционные формы (POST) для совместимости с модулем
 * - JSON API (Accept: application/json или ?api=1) для AJAX
 */

// Главная: список флагов (форма + таблица)
Route::get('', [FlagAdminController::class, 'index'])
    ->name('featureFlags::index');

// Создание: форма
Route::get('create', [FlagAdminController::class, 'create'])
    ->name('featureFlags::create');

// Создание: обработка (форма -> POST)
Route::post('', [FlagAdminController::class, 'store'])
    ->name('featureFlags::store');

// Редактирование: форма
Route::get('edit/{name}', [FlagAdminController::class, 'edit'])
    ->where(['name' => '^[a-z][a-z0-9_]*$'])
    ->name('featureFlags::edit');

// Редактирование: обработка (форма -> POST)
Route::post('edit/{name}', [FlagAdminController::class, 'update'])
    ->where(['name' => '^[a-z][a-z0-9_]*$'])
    ->name('featureFlags::update');

// Удаление: форма с подтверждением -> POST
Route::post('edit/{name}/delete', [FlagAdminController::class, 'destroy'])
    ->where(['name' => '^[a-z][a-z0-9_]*$'])
    ->name('featureFlags::destroy');

// JSON API endpoints (опционально, для внешних интеграций)
//Route::prefix('api')->group(function () {
//    Route::get('', [FlagAdminController::class, 'indexApi']);
//    Route::post('', [FlagAdminController::class, 'storeApi']);
//    Route::put('{name}', [FlagAdminController::class, 'updateApi']);
//    Route::delete('{name}', [FlagAdminController::class, 'destroyApi']);
//});

// Статистика: список флагов с краткой сводкой
Route::get('statistics', [FlagStatisticsController::class, 'index'])
    ->name('featureFlags::statistics.index');

// Статистика: детальный просмотр по флагу
Route::get('statistics/{flagName}', [FlagStatisticsController::class, 'show'])
    ->where(['flagName' => '^[a-z][a-z0-9_]*$'])
    ->name('featureFlags::statistics.show');

// Статистика: экспорт в CSV
Route::get('statistics/{flagName}/export', [FlagStatisticsController::class, 'export'])
    ->where(['flagName' => '^[a-z][a-z0-9_]*$'])
    ->name('featureFlags::statistics.export');

// JSON API для статистики (опционально)
Route::get('api/statistics', [FlagStatisticsController::class, 'indexApi'])
    ->name('featureFlags::statistics.indexApi');

Route::get('api/statistics/{flagName}', [FlagStatisticsController::class, 'showApi'])
    ->where(['flagName' => '^[a-z][a-z0-9_]*$'])
    ->name('featureFlags::statistics.showApi');
