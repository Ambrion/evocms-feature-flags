<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Repository;

use EvolutionCMS\FeatureFlags\Application\DTO\AdminFlagDTO;
use EvolutionCMS\FeatureFlags\Infrastructure\Database\Models\EloquentFeatureFlag;
use EvolutionCMS\FeatureFlags\Infrastructure\Repository\FlagAdminEloquentRepository;
use Illuminate\Database\Capsule\Manager as Capsule;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @covers \EvolutionCMS\FeatureFlags\Infrastructure\Repository\FlagAdminEloquentRepository
 */
final class FlagAdminEloquentRepositoryTest extends TestCase
{
    private FlagAdminEloquentRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Поднимаем Eloquent in-memory на SQLite
        $capsule = new Capsule();
        $capsule->addConnection([
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        // 2. Создаём таблицу строго по миграции
        Capsule::schema()->create('feature_flags', static function ($table) {
            $table->string('name', 100)->primary();
            $table->boolean('default_value')->default(false);
            $table->json('rules')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->boolean('log_statistics')->default(false);
            $table->timestamps();
        });

        // 3. Инстанцируем репозиторий
        $this->repository = new FlagAdminEloquentRepository(new EloquentFeatureFlag());
    }

    protected function tearDown(): void
    {
        Capsule::schema()->dropIfExists('feature_flags');
        parent::tearDown();
    }

    #[Test]
    public function it_creates_flag_and_returns_valid_dto(): void
    {
        $dto = new AdminFlagDTO(
            name: 'show_new_gallery_block',
            default_value: true,
            rules: [['condition' => 'category=products', 'value' => true]],
            description: 'Тестовый галерея-блок',
            log_statistics: true
        );

        $result = $this->repository->create($dto);

        self::assertSame('show_new_gallery_block', $result->name);
        self::assertTrue($result->default_value);
        self::assertTrue($result->log_statistics);
        self::assertNotNull($this->repository->findByName('show_new_gallery_block'));
    }

    #[Test]
    public function it_returns_all_flags_as_associative_array(): void
    {
        $this->repository->create(new AdminFlagDTO(name: 'flag_a', is_active: true));
        $this->repository->create(new AdminFlagDTO(name: 'flag_b', is_active: false));
        $this->repository->create(new AdminFlagDTO(name: 'flag_c', is_active: true));

        $list = $this->repository->list();

        self::assertCount(3, $list);
        self::assertArrayHasKey('flag_a', $list);
        self::assertArrayHasKey('flag_c', $list);
        self::assertFalse($list['flag_a']->log_statistics);
    }

    #[Test]
    public function it_updates_existing_flag_correctly(): void
    {
        $this->repository->create(new AdminFlagDTO(name: 'old_flag', default_value: false));

        $updatedDto = new AdminFlagDTO(
            name: 'old_flag',
            default_value: true,
            description: 'Updated',
            log_statistics: true
        );
        $result = $this->repository->update('old_flag', $updatedDto);

        self::assertTrue($result->default_value);
        self::assertSame('Updated', $result->description);
        self::assertTrue($result->log_statistics);
    }

    #[Test]
    public function it_deletes_flag_by_primary_key(): void
    {
        $this->repository->create(new AdminFlagDTO(name: 'to_delete'));
        self::assertNotNull($this->repository->findByName('to_delete'));

        $this->repository->delete('to_delete');

        self::assertNull($this->repository->findByName('to_delete'));
    }

    #[Test]
    public function it_reports_is_writable_as_true_for_db_storage(): void
    {
        self::assertTrue($this->repository->isWritable());
    }

    #[Test]
    public function it_persists_log_statistics_setting(): void
    {
        $dto = new AdminFlagDTO(
            name: 'stats_flag',
            log_statistics: true
        );

        $created = $this->repository->create($dto);
        $fetched = $this->repository->findByName('stats_flag');

        self::assertTrue($fetched->log_statistics);

        // Обновление на false
        $updated = $this->repository->update('stats_flag', new AdminFlagDTO(
            name: 'stats_flag',
            log_statistics: false
        ));

        self::assertFalse($updated->log_statistics);
    }
}
