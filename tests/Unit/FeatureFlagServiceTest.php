<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Tests\Unit;

use FeatureFlags\Core\Application\Service\FeatureFlagService;
use FeatureFlags\Core\Domain\Repository\FlagRepositoryInterface;
use FeatureFlags\Core\Domain\Logger\NullFlagUsageLogger;
use FeatureFlags\Core\Domain\ValueObject\FlagName;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class FeatureFlagServiceTest extends TestCase
{
    private FeatureFlagService $service;
    private FlagRepositoryInterface $repositoryMock;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(FlagRepositoryInterface::class);
        $this->service = new FeatureFlagService(
            $this->repositoryMock,
            new NullFlagUsageLogger()
        );
    }

    #[Test]
    public function it_returns_false_when_flag_is_not_found(): void
    {
        // Arrange: Готовим имя флага как Value Object
        $flagName = new FlagName('unknown_flag');

        // Mock: Репозиторий вернёт null (флаг не найден)
        $this->repositoryMock
            ->expects($this->once())
            ->method('findByName')
            ->with($flagName)
            ->willReturn(null);

        // Act
        $result = $this->service->isEnabled('unknown_flag');

        // Assert
        $this->assertFalse($result);
    }
}
