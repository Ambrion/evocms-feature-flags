<?php declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Tests\Unit\Infrastructure\Logger;

use EvolutionCMS\FeatureFlags\Application\DTO\AdminFlagDTO;
use EvolutionCMS\FeatureFlags\Domain\Repository\FlagAdminRepositoryInterface;
use EvolutionCMS\FeatureFlags\Domain\Repository\FlagStatisticsRepositoryInterface;
use EvolutionCMS\FeatureFlags\Infrastructure\Logger\DatabaseFlagUsageLogger;
use FeatureFlags\Core\Domain\ValueObject\EvaluationResult;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

final class DatabaseFlagUsageLoggerTest extends TestCase
{
    #[Before]
    public function resetRemoteAddr(): void
    {
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    #[After]
    public function clearRemoteAddr(): void
    {
        unset($_SERVER['REMOTE_ADDR']);
    }

    #[Test]
    public function it_delegates_save_to_repository_with_correct_record(): void
    {
        // ARRANGE
        $statsRepo = $this->createMock(FlagStatisticsRepositoryInterface::class);
        $flagAdminRepo = $this->createMock(FlagAdminRepositoryInterface::class);

        $flagDto = new AdminFlagDTO(
            name: 'test_flag',
            default_value: false,
            rules: [],
            is_active: true,
            description: null,
            log_statistics: true
        );
        $flagAdminRepo->expects(new InvokedCount(1))
            ->method('findByName')
            ->with('test_flag')
            ->willReturn($flagDto);

        $statsRepo->expects(new InvokedCount(1))
            ->method('save')
            ->with($this->callback(function ($record) {
                return $record->flagName === 'test_flag'
                    && $record->result === true
                    && strlen($record->contextHash) === 32
                    && $record->ip === '127.0.0.1'
                    && $record->variant === 'test_variant'
                    && $record->weight === 0.33;
            }));

        $logger = new DatabaseFlagUsageLogger($statsRepo, $flagAdminRepo);

        // ACT
        $result = new EvaluationResult(
            enabled: true,
            variant: 'test_variant',
            weight: 0.33,
            matchedRule: 'user_role=admin'
        );
        $logger->logEvaluation('test_flag', $result, ['user_role' => 'admin']);
    }

    #[AllowMockObjectsWithoutExpectations]
    #[Test]
    public function it_silently_fails_when_repository_throws(): void
    {
        // ARRANGE
        $statsRepo = $this->createMock(FlagStatisticsRepositoryInterface::class);
        $statsRepo->method('save')->willThrowException(new RuntimeException('DB down'));

        $errorLogger = $this->createMock(LoggerInterface::class);
        $errorLogger->expects(new InvokedCount(1))
            ->method('error')
            ->with($this->stringContains('[DatabaseFlagUsageLogger]'));

        $logger = new DatabaseFlagUsageLogger($statsRepo, null, $errorLogger);

        // ACT
        $result = new EvaluationResult(enabled: false);
        $logger->logEvaluation('any_flag', $result, []);
    }

    #[AllowMockObjectsWithoutExpectations]
    #[Test]
    public function it_logs_null_values_when_result_has_nulls(): void
    {
        // ARRANGE
        $statsRepo = $this->createMock(FlagStatisticsRepositoryInterface::class);
        $flagAdminRepo = $this->createMock(FlagAdminRepositoryInterface::class);

        $flagDto = new AdminFlagDTO(name: 'null_test', log_statistics: true);
        $flagAdminRepo->method('findByName')->willReturn($flagDto);

        $statsRepo->expects(new InvokedCount(1))
            ->method('save')
            ->with($this->callback(function ($record) {
                return $record->flagName === 'null_test'
                    && $record->variant === null
                    && $record->weight === null;
            }));

        $logger = new DatabaseFlagUsageLogger($statsRepo, $flagAdminRepo);

        // ACT
        $result = new EvaluationResult(enabled: null, variant: null, weight: null);
        $logger->logEvaluation('null_test', $result, ['debug' => true]);
    }

    #[AllowMockObjectsWithoutExpectations]
    #[Test]
    public function it_skips_logging_when_flag_log_statistics_disabled(): void
    {
        // ARRANGE
        $statsRepo = $this->createMock(FlagStatisticsRepositoryInterface::class);
        $flagAdminRepo = $this->createMock(FlagAdminRepositoryInterface::class);

        // Флаг с отключённым логированием
        $flagDto = new AdminFlagDTO(name: 'silent_flag', log_statistics: false);
        $flagAdminRepo->method('findByName')->willReturn($flagDto);

        // save() НЕ должен быть вызван
        $statsRepo->expects(new InvokedCount(0))->method('save');

        $logger = new DatabaseFlagUsageLogger($statsRepo, $flagAdminRepo);

        // ACT
        $result = new EvaluationResult(enabled: true);
        $logger->logEvaluation('silent_flag', $result, []);

        // ASSERT: expectation verified by PHPUnit
    }
}
