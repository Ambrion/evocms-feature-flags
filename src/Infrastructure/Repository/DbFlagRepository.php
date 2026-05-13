<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Infrastructure\Repository;

use FeatureFlags\Core\Domain\Entity\FeatureFlag;
use FeatureFlags\Core\Domain\Repository\FlagRepositoryInterface;
use FeatureFlags\Core\Domain\Specification\ConditionSpecificationInterface;
use FeatureFlags\Core\Domain\ValueObject\FlagName;
use PDO;

/**
 * Инфраструктурный адаптер: читает флаги из базы данных.
 *
 * Не зависит от конкретного драйвера БД (PDO, $evo->db, Eloquent).
 * Принимает колбэк для фетчинга -> легко тестируется и переиспользуется.
 */
final readonly class DbFlagRepository implements FlagRepositoryInterface
{
    /**
     * @param callable(string): ?array<string, mixed> $fetchCallback
     * @param ConditionSpecificationInterface[] $specifications
     */
    public function __construct(
        private mixed $fetchCallback,
        private array $specifications = []
    )
    {
    }

    public function findByName(FlagName $flagName): ?FeatureFlag
    {
        // 1. Получаем сырые данные из БД через колбэк
        $row = ($this->fetchCallback)($flagName->value);

        // 2. Флаг не найден или деактивирован -> возвращаем null
        if ($row === null || !($row['is_active'] ?? true)) {
            return null;
        }

        // 3. Маппим БД-строку в доменную сущность
        $rules = is_string($row['rules'] ?? '')
            ? json_decode($row['rules'], true) ?: []
            : (array)($row['rules'] ?? []);

        return new FeatureFlag(
            name: $flagName,
            default: (bool)($row['default_value'] ?? false),
            rules: $rules,
            specifications: $this->specifications
        );
    }

    /**
     * Фабричный метод для создания репозитория с PDO.
     * Удобно для регистрации в контейнере.
     */
    public static function withPdo(
        PDO   $pdo,
        string $table = 'modx_feature_flags',
        array  $specifications = []
    ): self
    {
        return new self(
            fetchCallback: function (string $flagName) use ($pdo, $table): ?array {
                $stmt = $pdo->prepare(
                    "SELECT name,
                                  default_value,
                                  rules,
                                  is_active
                          FROM {$table}
                          WHERE name = :name
                            AND is_active = 1
                          LIMIT 1"
                );
                $stmt->execute(['name' => $flagName]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                return $row ?: null;
            },
            specifications: $specifications
        );
    }

    /**
     * Фабричный метод для создания репозитория с $evo->db.
     * Для совместимости с Evolution CMS 3.x.
     */
    public static function withEvoDb(
        object $evoDb,
        string $table = 'modx_feature_flags',
        array  $specifications = []
    ): self
    {
        return new self(
            fetchCallback: function (string $flagName) use ($evoDb, $table): ?array {
                // Адаптер под API $evo->db (предполагаем наличие prepare/execute/query)
                if (method_exists($evoDb, 'prepare')) {
                    $stmt = $evoDb->prepare(
                        "SELECT name,
                                default_value,
                                rules,
                                is_active
                         FROM {$table}
                         WHERE name = :name
                           AND is_active = 1
                         LIMIT 1"
                    );
                    $stmt->execute(['name' => $flagName]);

                    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
                }

                // Фолбэк на старый API (если есть)
                if (method_exists($evoDb, 'query')) {
                    $sql = "SELECT name,
                                   default_value,
                                   rules,
                                   is_active
                            FROM {$table}
                            WHERE name = '{$evoDb->escape($flagName)}'
                              AND is_active = 1
                            LIMIT 1";
                    $result = $evoDb->query($sql);

                    return $result ? $evoDb->getRow($result) : null;
                }

                return null;
            },
            specifications: $specifications
        );
    }
}
