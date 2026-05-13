<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Infrastructure\Repository;

use FeatureFlags\Core\Domain\Entity\FeatureFlag;
use FeatureFlags\Core\Domain\Repository\FlagRepositoryInterface;
use FeatureFlags\Core\Domain\Specification\ConditionSpecificationInterface;
use FeatureFlags\Core\Domain\ValueObject\FlagName;

/**
 * Инфраструктурный адаптер: лениво загружает флаги из PHP-конфига.
 * Зависит только от доменных контрактов. Не использует $modx, БД или глобальные функции.
 */
final readonly class ConfigFileFlagRepository implements FlagRepositoryInterface
{
    /**
     * @param ConditionSpecificationInterface[] $specifications
     */
    public function __construct(
        private string $configPath,
        private array  $specifications = []
    )
    {
    }

    public function findByName(FlagName $flagName): ?FeatureFlag
    {
        // 1. Проверяем существование файла
        if (!file_exists($this->configPath)) {
            return null;
        }

        // 2. Лениво загружаем конфигурацию
        $flagsConfig = $this->loadConfig();
        if (empty($flagsConfig)) {
            return null;
        }

        // 3. Ищем флаг по имени
        $key = $flagName->value;
        if (!isset($flagsConfig[$key])) {
            return null;
        }

        $config = $flagsConfig[$key];

        // 4. Маппим в доменную сущность
        return new FeatureFlag(
            name: $flagName,
            default: (bool)($config['default'] ?? false),
            rules: $config['rules'] ?? [],
            specifications: $this->specifications
        );
    }

    /**
     * Загружает массив конфигурации из файла.
     * Возвращает пустой массив при ошибке чтения/парсинга.
     */
    private function loadConfig(): array
    {
        try {
            $config = require $this->configPath;

            return is_array($config) ? $config : [];
        } catch (\Throwable $e) {
            // В продакшене здесь уместно логирование через FlagUsageLoggerInterface
            return [];
        }
    }
}
