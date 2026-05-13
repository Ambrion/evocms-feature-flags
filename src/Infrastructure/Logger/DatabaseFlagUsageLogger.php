<?php declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Infrastructure\Logger;

use EvolutionCMS\FeatureFlags\Domain\Repository\FlagAdminRepositoryInterface;
use EvolutionCMS\FeatureFlags\Domain\Repository\FlagStatisticsRepositoryInterface;
use EvolutionCMS\FeatureFlags\Domain\ValueObject\FlagStatisticsRecord;
use FeatureFlags\Core\Domain\Logger\FlagUsageLoggerInterface;
use FeatureFlags\Core\Domain\ValueObject\EvaluationResult;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

/**
 * Инфраструктурный логгер: сохраняет оценку флагов в БД.
 * Реализует контракт ядра через единый метод logEvaluation().
 */
final readonly class DatabaseFlagUsageLogger implements FlagUsageLoggerInterface
{
    public function __construct(
        private FlagStatisticsRepositoryInterface $repository,
        private ?FlagAdminRepositoryInterface     $flagAdminRepo = null,
        private LoggerInterface                   $errorLogger = new NullLogger()
    )
    {
    }

    /**
     * Логирует результат оценки флага в базу данных.
     *
     * @param string $flagName Имя флага
     * @param EvaluationResult $result Структурированный результат оценки
     * @param array<string, scalar|null> $context Контекст вызова
     */
    public function logEvaluation(string $flagName, EvaluationResult $result, array $context = []): void
    {
        try {
            $globalEnabled = $this->isLoggingGloballyEnabled();
            if (!$globalEnabled) {
                return;
            }

            if (!$this->isFlagLoggingEnabled($flagName)) {
                return;
            }

            $resultBool = $result->enabled ?? ($result->variant !== null);

            $record = FlagStatisticsRecord::fromEvaluation(
                flagName: $flagName,
                result: $resultBool,
                context: $context,
                variant: $result->variant,
                weight: $result->weight,
                matchedRule: $result->matchedRule,
            );
            $this->repository->save($record);

        } catch (Throwable $e) {
            $this->errorLogger->error('[DatabaseFlagUsageLogger] ' . $e->getMessage(), [
                'flag' => $flagName,
                'exception' => $e,
            ]);
        }
    }

    /**
     * Проверяет глобальную настройку логирования.
     */
    private function isLoggingGloballyEnabled(): bool
    {
        if (!function_exists('config')) {
            return true;
        }

        try {
            return (bool)config('feature_flags.log_statistics', true);
        } catch (Throwable) {
            return true;
        }
    }

    /**
     * Проверяет, разрешено ли логирование для конкретного флага.
     */
    private function isFlagLoggingEnabled(string $flagName): bool
    {
        if ($this->flagAdminRepo === null) {
            return true;
        }

        $flag = $this->flagAdminRepo->findByName($flagName);

        return $flag !== null && $flag->log_statistics;
    }
}
