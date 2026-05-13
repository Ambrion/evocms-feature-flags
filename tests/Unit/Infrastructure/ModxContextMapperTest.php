<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Tests\Unit\Infrastructure;

use EvolutionCMS\FeatureFlags\Infrastructure\Context\ModxContextMapper;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class ModxContextMapperTest extends TestCase
{
    #[Test]
    public function it_maps_modx_document_to_target_id_and_infers_environment(): void
    {
        // Имитируем данные, которые обычно приходят из $modx
        $rawData = [
            'documentIdentifier' => 42,
            'user_role'          => 'manager',
            'site_status'        => 1, // сайт онлайн
        ];

        $mapper = new ModxContextMapper($rawData);
        $context = $mapper->build();

        // 🔹 Проверяем, что возвращается массив
        $this->assertIsArray($context);

        // Проверяем маппинг ключей
        $this->assertSame('42', $context['target_id']);
        $this->assertSame('manager', $context['user_role']);
        $this->assertSame('production', $context['environment']);

        // Проверяем авто-генерацию даты
        $this->assertSame(date('Y-m-d'), $context['current_date']);
    }

    #[Test]
    public function it_preserves_custom_context_keys(): void
    {
        $rawData = [
            'documentIdentifier' => 101,
            'category'           => 'electronics',
            'user_hash'          => 'custom_hash_123',
        ];

        $mapper = new ModxContextMapper($rawData);
        $context = $mapper->build();

        $this->assertSame('electronics', $context['category']);
        $this->assertSame('custom_hash_123', $context['user_hash']);
        // Хеш не перегенерирован, если передан явно
    }

    #[Test]
    public function it_generates_user_hash_when_not_provided(): void
    {
        $rawData = [
            'ip' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
        ];

        $mapper = new ModxContextMapper($rawData);
        $context = $mapper->build();

        $expectedHash = md5('192.168.1.1' . 'Mozilla/5.0');
        $this->assertSame($expectedHash, $context['user_hash']);
    }

    #[Test]
    public function it_handles_empty_data_without_throwing(): void
    {
        $mapper = new ModxContextMapper([]);
        $context = $mapper->build();

        $this->assertIsArray($context);

        // Проверяем, что авто-значения подставились
        $this->assertArrayHasKey('current_date', $context);
        $this->assertArrayHasKey('environment', $context);
        $this->assertArrayHasKey('user_hash', $context);

        // Проверяем, что необязательные ключи отсутствуют или null
        $this->assertArrayNotHasKey('target_id', $context);
        $this->assertArrayNotHasKey('user_role', $context);
    }

    #[Test]
    public function it_skips_internal_keys_from_raw_data(): void
    {
        $rawData = [
            'documentIdentifier' => 42,
            'site_status' => 0,
            'ip' => '127.0.0.1',
            'user_agent' => 'Test',
            'custom_key' => 'custom_value',
        ];

        $mapper = new ModxContextMapper($rawData);
        $context = $mapper->build();

        // Внутренние ключи не должны попадать в результат
        $this->assertArrayNotHasKey('documentIdentifier', $context);
        $this->assertArrayNotHasKey('site_status', $context);
        $this->assertArrayNotHasKey('ip', $context);
        $this->assertArrayNotHasKey('user_agent', $context);

        // Но их маппинг должен присутствовать
        $this->assertSame('42', $context['target_id']);
        $this->assertSame('maintenance', $context['environment']);

        // Кастомные ключи пробрасываются
        $this->assertSame('custom_value', $context['custom_key']);
    }

    #[Test]
    public function it_overrides_auto_values_with_explicit_data(): void
    {
        $rawData = [
            'current_date' => '2024-12-25',
            'environment' => 'staging',
            'user_hash' => 'explicit_hash',
        ];

        $mapper = new ModxContextMapper($rawData);
        $context = $mapper->build();

        // Явные значения должны победить авто-генерацию
        $this->assertSame('2024-12-25', $context['current_date']);
        $this->assertSame('staging', $context['environment']);
        $this->assertSame('explicit_hash', $context['user_hash']);
    }
}
