<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Tests\Unit;

use EvolutionCMS\FeatureFlags\Infrastructure\Repository\ConfigFlagRepository;
use FeatureFlags\Core\Application\Service\FeatureFlagService;
use FeatureFlags\Core\Domain\Logger\NullFlagUsageLogger;
use FeatureFlags\Core\Domain\Specification\TargetIdSpecification;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class FeatureFlagEvaluationTest extends TestCase
{
    #[Test]
    public function it_enables_flag_when_target_id_matches_rule(): void
    {
        // Arrange: Конфиг с правилом для target_id=42
        $config = [
            'show_premium_badge' => [
                'default' => false,
                'rules' => [
                    ['condition' => 'target_id=42', 'value' => true],
                ],
            ],
        ];

        // Спецификации, которые "понимают" условия из правил
        $specifications = [new TargetIdSpecification()];

        // Репозиторий с внедрёнными спецификациями
        $repository = new ConfigFlagRepository($config, $specifications);
        $service = new FeatureFlagService($repository, new NullFlagUsageLogger());

        // Act: Контекст совпадает с правилом
        $result = $service->isEnabled('show_premium_badge', [
            'target_id' => 42,
        ]);

        // Assert: Правило сработало -> вернулось значение из правила (true), а не default (false)
        $this->assertTrue($result);
    }

    #[Test]
    public function it_returns_default_when_target_id_does_not_match(): void
    {
        $config = [
            'show_premium_badge' => [
                'default' => false,
                'rules' => [
                    ['condition' => 'target_id=42', 'value' => true],
                ],
            ],
        ];

        $specifications = [new TargetIdSpecification()];
        $repository = new ConfigFlagRepository($config, $specifications);
        $service = new FeatureFlagService($repository, new NullFlagUsageLogger());

        // Контекст НЕ совпадает с правилом -> должен вернуться default
        $result = $service->isEnabled('show_premium_badge', [
            'target_id' => 99,
        ]);

        $this->assertFalse($result);
    }

    #[Test]
    public function it_supports_target_id_in_list(): void
    {
        $config = [
            'show_new_gallery' => [
                'default' => false,
                'rules' => [
                    ['condition' => 'target_id IN (10,20,30)', 'value' => true],
                ],
            ],
        ];

        $specifications = [new TargetIdSpecification()];
        $repository = new ConfigFlagRepository($config, $specifications);
        $service = new FeatureFlagService($repository, new NullFlagUsageLogger());

        // target_id=20 есть в списке -> правило срабатывает
        $result = $service->isEnabled('show_new_gallery', [
            'target_id' => 20,
        ]);

        $this->assertTrue($result);
    }
}
