<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Tests\Unit\Infrastructure;

use EvolutionCMS\FeatureFlags\Infrastructure\Repository\ConfigFlagRepository;
use FeatureFlags\Core\Domain\Entity\FeatureFlag;
use FeatureFlags\Core\Domain\Repository\FlagRepositoryInterface;
use FeatureFlags\Core\Domain\ValueObject\FlagName;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class ConfigFlagRepositoryTest extends TestCase
{
    private FlagRepositoryInterface $repository;

    protected function setUp(): void
    {
        // Имитируем конфиг, который в будущем будет подтягиваться из config/feature_flags.php
        $flagsConfig = [
            'new_product_template' => [
                'default' => false,
                'rules' => [
                    ['condition' => 'environment=dev', 'value' => true]
                ]
            ],
            'show_winter_banner' => [
                'default' => true,
                'rules' => []
            ]
        ];

        $this->repository = new ConfigFlagRepository($flagsConfig);
    }

    #[Test]
    public function it_returns_feature_flag_when_name_exists_in_config(): void
    {
        $flagName = new FlagName('show_winter_banner');
        $result = $this->repository->findByName($flagName);

        $this->assertInstanceOf(FeatureFlag::class, $result);
        $this->assertEquals($flagName, $result->name);
        $this->assertTrue($result->default);
    }

    #[Test]
    public function it_returns_null_for_unknown_flag_name(): void
    {
        $flagName = new FlagName('unknown_experimental_flag');
        $result = $this->repository->findByName($flagName);

        $this->assertNull($result);
    }
}
