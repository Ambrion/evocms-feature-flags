<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Tests\Unit\Infrastructure\Repository;

use EvolutionCMS\FeatureFlags\Infrastructure\Repository\ConfigFileFlagRepository;
use FeatureFlags\Core\Domain\Entity\FeatureFlag;
use FeatureFlags\Core\Domain\Specification\CategorySpecification;
use FeatureFlags\Core\Domain\ValueObject\FlagName;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class ConfigFileFlagRepositoryTest extends TestCase
{
    private string $tempConfigFile;

    protected function setUp(): void
    {
        parent::setUp();
        // Создаём временный файл с тестовым конфигом
        $this->tempConfigFile = sys_get_temp_dir() . '/feature_flags_test_' . uniqid() . '.php';

        $testConfig = [
            'test_flag' => [
                'default' => true,
                'rules' => [
                    ['condition' => 'category=electronics', 'value' => false],
                ],
            ],
            'another_flag' => [
                'default' => false,
                'rules' => [],
            ],
        ];

        file_put_contents(
            $this->tempConfigFile,
            "<?php return " . var_export($testConfig, true) . ";"
        );
    }

    protected function tearDown(): void
    {
        // Удаляем временный файл после теста
        if (file_exists($this->tempConfigFile)) {
            unlink($this->tempConfigFile);
        }
        parent::tearDown();
    }

    #[Test]
    public function it_loads_flag_from_config_file(): void
    {
        $specifications = [new CategorySpecification()];
        $repository = new ConfigFileFlagRepository($this->tempConfigFile, $specifications);

        $flag = $repository->findByName(new FlagName('test_flag'));

        $this->assertInstanceOf(FeatureFlag::class, $flag);
        $this->assertEquals('test_flag', $flag->name->value);
        $this->assertTrue($flag->default);
        $this->assertCount(1, $flag->rules);
    }

    #[Test]
    public function it_returns_null_for_unknown_flag(): void
    {
        $specifications = [];
        $repository = new ConfigFileFlagRepository($this->tempConfigFile, $specifications);

        $flag = $repository->findByName(new FlagName('nonexistent_flag'));

        $this->assertNull($flag);
    }

    #[Test]
    public function it_returns_null_when_config_file_not_exists(): void
    {
        $specifications = [];
        $repository = new ConfigFileFlagRepository('/path/to/nonexistent.php', $specifications);

        $flag = $repository->findByName(new FlagName('any_flag'));

        $this->assertNull($flag);
    }

    #[Test]
    public function it_passes_specifications_to_feature_flag(): void
    {
        $specifications = [new CategorySpecification()];
        $repository = new ConfigFileFlagRepository($this->tempConfigFile, $specifications);

        $flag = $repository->findByName(new FlagName('test_flag'));

        // Проверяем, что спецификации переданы в сущность (через рефлексию, т.к. свойство private)
        $reflection = new \ReflectionClass($flag);
        $prop = $reflection->getProperty('specifications');
        $flagSpecs = $prop->getValue($flag);

        $this->assertCount(1, $flagSpecs);
        $this->assertInstanceOf(CategorySpecification::class, $flagSpecs[0]);
    }
}
