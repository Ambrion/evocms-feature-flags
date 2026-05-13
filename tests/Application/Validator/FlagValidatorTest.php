<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Tests\Application\Validator;

use EvolutionCMS\FeatureFlags\Application\Exceptions\FlagValidationException;
use EvolutionCMS\FeatureFlags\Application\Validator\FlagValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @covers \EvolutionCMS\FeatureFlags\Application\Validator\FlagValidator
 */
final class FlagValidatorTest extends TestCase
{
    private FlagValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new FlagValidator();
    }

    #[Test]
    public function it_passes_valid_create_data(): void
    {
        $this->validator->validateCreate([
            'name' => 'show_new_block',
            'default_value' => true,
            'is_active' => true,
            'description' => 'Test block',
        ]);
        // Если исключение не выброшено — тест пройден
        $this->assertTrue(true);
    }

    #[Test]
    public function it_fails_on_invalid_name_format(): void
    {
        $this->expectException(FlagValidationException::class);
        $this->expectExceptionMessage('Validation failed');

        $this->validator->validateCreate(['name' => 'Invalid Name!']);
    }

    #[Test]
    public function it_fails_on_missing_name(): void
    {
        $this->expectException(FlagValidationException::class);
        $this->validator->validateCreate(['default_value' => true]);
    }

    #[Test]
    public function it_fails_on_name_too_long(): void
    {
        $this->expectException(FlagValidationException::class);
        $this->validator->validateCreate(['name' => str_repeat('a', 101)]);
    }

    #[Test]
    public function it_validates_update_payload(): void
    {
        // В update имя не проверяется (оно в URL), но другие поля должны быть валидны
        $this->validator->validateUpdate([
            'default_value' => false,
            'description' => 'Updated',
        ]);
        $this->assertTrue(true);
    }
}
