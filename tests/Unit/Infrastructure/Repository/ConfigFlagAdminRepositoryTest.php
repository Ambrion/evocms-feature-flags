<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Tests\Unit\Infrastructure\Repository;

use EvolutionCMS\FeatureFlags\Application\DTO\AdminFlagDTO;
use EvolutionCMS\FeatureFlags\Infrastructure\Repository\ConfigFlagAdminRepository;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

final class ConfigFlagAdminRepositoryTest extends TestCase
{
    private string $tempConfigFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempConfigFile = sys_get_temp_dir() . '/ff_test_' . uniqid() . '.php';

        $config = [
            'show_promo' => ['default' => true, 'rules' => [['condition' => 'date BETWEEN 01-01 AND 01-31', 'value' => true]]],
            'dark_mode' => ['default' => false, 'rules' => []],
        ];

        file_put_contents($this->tempConfigFile, "<?php return " . var_export($config, true) . ";");
    }

    protected function tearDown(): void
    {
        if (file_exists($this->tempConfigFile)) {
            unlink($this->tempConfigFile);
        }
        parent::tearDown();
    }

    #[Test]
    public function it_lists_all_flags_as_dtos(): void
    {
        $repo = new ConfigFlagAdminRepository($this->tempConfigFile);
        $list = $repo->list();

        $this->assertCount(2, $list);
        $this->assertContainsOnlyInstancesOf(AdminFlagDTO::class, $list);
        $this->assertSame('show_promo', $list['show_promo']->name);
    }

    #[Test]
    public function it_finds_flag_by_name(): void
    {
        $repo = new ConfigFlagAdminRepository($this->tempConfigFile);
        $flag = $repo->findByName('dark_mode');

        $this->assertInstanceOf(AdminFlagDTO::class, $flag);
        $this->assertFalse($flag->default_value);
        $this->assertEmpty($flag->rules);
    }

    #[Test]
    public function it_returns_null_for_unknown_flag(): void
    {
        $repo = new ConfigFlagAdminRepository($this->tempConfigFile);
        $flag = $repo->findByName('nonexistent');

        $this->assertNull($flag);
    }

    #[Test]
    public function it_is_not_writable(): void
    {
        $repo = new ConfigFlagAdminRepository($this->tempConfigFile);
        $this->assertFalse($repo->isWritable());
    }

    #[Test]
    public function it_throws_on_create(): void
    {
        $repo = new ConfigFlagAdminRepository($this->tempConfigFile);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Config provider is read-only. Use database repository for CRUD.');

        $repo->create(new AdminFlagDTO('test', false, []));
    }

    #[Test]
    public function it_throws_on_update_and_delete(): void
    {
        $repo = new ConfigFlagAdminRepository($this->tempConfigFile);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Config provider is read-only. Use database repository for CRUD.');
        $repo->update('any', new AdminFlagDTO('any', true, []));
    }
}
