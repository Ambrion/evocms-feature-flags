<?php
use FeatureFlags\Core\Application\Service\FeatureFlagService;
use FeatureFlags\Core\Domain\Repository\FlagRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class SmokeTest extends TestCase
{
    public function test_core_is_loaded_in_evo_context(): void
    {
        $this->assertTrue(interface_exists(FlagRepositoryInterface::class));
        $this->assertTrue(class_exists(FeatureFlagService::class));
    }
}
