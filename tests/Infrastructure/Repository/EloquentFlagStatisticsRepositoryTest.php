<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Tests\Infrastructure\Repository;

use DateTimeImmutable;
use EvolutionCMS\FeatureFlags\Domain\ValueObject\FlagStatisticsFilter;
use EvolutionCMS\FeatureFlags\Domain\ValueObject\FlagStatisticsRecord;
use EvolutionCMS\FeatureFlags\Infrastructure\Model\EloquentFlagStatistics;
use EvolutionCMS\FeatureFlags\Infrastructure\Repository\EloquentFlagStatisticsRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Легковесный дубль QueryBuilder.
 * Имитирует флюент-интерфейс Eloquent, но не тянет зависимости БД.
 */
class FakeQueryBuilder
{
    public array $wheres = [];
    public ?string $orderBy = null;
    public ?string $orderByDir = null;
    public ?int $offset = null;
    public ?int $limit = null;
    public array $createdData = [];

    public function __construct(private Collection $collection, private int $countResult = 0) {}

    public function where(...$args): self { $this->wheres[] = $args; return $this; }
    public function whereBetween(...$args): self { $this->wheres[] = $args; return $this; }
    public function whereNotNull(...$args): self { $this->wheres[] = $args; return $this; }
    public function orderBy(string $column, string $dir = 'asc'): self { $this->orderBy = $column; $this->orderByDir = $dir; return $this; }
    public function offset(int $value): self { $this->offset = $value; return $this; }
    public function limit(int $value): self { $this->limit = $value; return $this; }
    public function selectRaw(string $sql): self { return $this; }
    public function groupBy(...$args): self { return $this; }
    public function get(): Collection { return $this->collection; }
    public function count(): int { return $this->countResult; }
    public function sum(string $column): int|float { return (int) $this->collection->sum($column); }
    public function create(array $data): void { $this->createdData = $data; }
}

class EloquentFlagStatisticsRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Model::unguard();
    }

    protected function tearDown(): void
    {
        Model::reguard();
        parent::tearDown();
    }

    #[Test]
    public function save_maps_record_to_array_and_calls_create(): void
    {
        $record = FlagStatisticsRecord::fromEvaluation(
            flagName: 'checkout_test', result: true,
            context: ['user_hash' => 'x1'], variant: 'B', weight: 0.34
        );

        $fakeBuilder = new FakeQueryBuilder(new Collection());

        $modelStub = $this->createStub(EloquentFlagStatistics::class);
        $modelStub->method('newQuery')->willReturn($fakeBuilder);

        $repo = new EloquentFlagStatisticsRepository($modelStub);
        $repo->save($record);

        $this->assertNotEmpty($fakeBuilder->createdData);
        $this->assertSame('checkout_test', $fakeBuilder->createdData['flag_name']);
        $this->assertTrue($fakeBuilder->createdData['result']);
        $this->assertSame('B', $fakeBuilder->createdData['variant']);
        $this->assertEquals(0.34, $fakeBuilder->createdData['weight']);
        $this->assertSame(md5(json_encode($record->context)), $fakeBuilder->createdData['context_hash']);
    }

    #[Test]
    public function findByFilter_applies_conditions_and_returns_mapped_records(): void
    {
        $modelStub = (new EloquentFlagStatistics())->setRawAttributes([
            'id' => 1, 'flag_name' => 'ab_test', 'result' => true,
            'variant' => 'A', 'weight' => 0.34, 'context_hash' => 'hash1',
            'ip' => '127.0.0.1', 'evaluated_at' => new DateTimeImmutable(),
            'context' => json_encode(['user_hash' => 'x1']),
        ], true);

        $collection = new Collection([$modelStub]);
        $fakeBuilder = new FakeQueryBuilder($collection);

        $modelQueryStub = $this->createStub(EloquentFlagStatistics::class);
        $modelQueryStub->method('newQuery')->willReturn($fakeBuilder);

        $repo = new EloquentFlagStatisticsRepository($modelQueryStub);
        $filter = FlagStatisticsFilter::byFlagName('ab_test')->withPagination(10, 0);

        $results = $repo->findByFilter($filter);

        $this->assertCount(1, $fakeBuilder->wheres);
        $this->assertSame('evaluated_at', $fakeBuilder->orderBy);
        $this->assertSame('desc', $fakeBuilder->orderByDir);
        $this->assertSame(10, $fakeBuilder->limit);
        $this->assertSame(0, $fakeBuilder->offset);

        $this->assertCount(1, $results);
        $record = $results[0];
        $this->assertInstanceOf(FlagStatisticsRecord::class, $record);
        $this->assertSame('ab_test', $record->flagName);
        $this->assertSame('A', $record->variant);
        $this->assertEquals(0.34, $record->weight);
        $this->assertIsArray($record->context);
        $this->assertSame('x1', $record->context['user_hash']);
    }

    #[Test]
    public function getVariantDistribution_returns_correct_percentages(): void
    {
        $aModel = (new EloquentFlagStatistics())->setRawAttributes(['variant' => 'A', 'count' => 34], true);
        $bModel = (new EloquentFlagStatistics())->setRawAttributes(['variant' => 'B', 'count' => 33], true);
        $cModel = (new EloquentFlagStatistics())->setRawAttributes(['variant' => 'C', 'count' => 33], true);

        $collection = new Collection([$aModel, $bModel, $cModel]);
        $fakeBuilder = new FakeQueryBuilder($collection);

        $modelStub = $this->createStub(EloquentFlagStatistics::class);
        $modelStub->method('newQuery')->willReturn($fakeBuilder);

        $repo = new EloquentFlagStatisticsRepository($modelStub);
        $from = new DateTimeImmutable('-1 day');
        $to = new DateTimeImmutable();

        $dist = $repo->getVariantDistribution('promo_test', $from, $to);

        $this->assertArrayHasKey('A', $dist);
        $this->assertSame(34, $dist['A']['count']);
        $this->assertEquals(34.0, $dist['A']['percentage']);

        $this->assertArrayHasKey('B', $dist);
        $this->assertSame(33, $dist['B']['count']);
        $this->assertEquals(33.0, $dist['B']['percentage']);

        $whereCalls = array_map(fn($args) => $args[0], $fakeBuilder->wheres);
        $this->assertContains('evaluated_at', $whereCalls);
        $this->assertContains('variant', $whereCalls);
    }

    #[Test]
    public function getTotalEvaluations_returns_correct_count(): void
    {
        $fakeBuilder = new FakeQueryBuilder(new Collection(), countResult: 42);

        $modelStub = $this->createStub(EloquentFlagStatistics::class);
        $modelStub->method('newQuery')->willReturn($fakeBuilder);

        $repo = new EloquentFlagStatisticsRepository($modelStub);
        $since = new DateTimeImmutable('-7 days');

        $total = $repo->getTotalEvaluations('header_test', $since);

        $this->assertSame(42, $total);
        $this->assertContains('flag_name', array_map(fn($a) => $a[0], $fakeBuilder->wheres));
        $this->assertContains('evaluated_at', array_map(fn($a) => $a[0], $fakeBuilder->wheres));
    }
}
