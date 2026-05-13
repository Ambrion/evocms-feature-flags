<?php

declare(strict_types=1);

namespace EvolutionCMS\FeatureFlags\Presentation\Http\Controllers\Admin;

use DateMalformedStringException;
use DateTimeImmutable;
use EvolutionCMS\FeatureFlags\Application\Service\FlagStatisticsService;
use EvolutionCMS\FeatureFlags\Domain\Repository\FlagAdminRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;

class FlagStatisticsController extends Controller
{
    public function __construct(
        private readonly FlagStatisticsService        $statsService,
        private readonly FlagAdminRepositoryInterface $flagAdminRepo
    )
    {
    }

    /**
     * Главная страница статистики: список флагов с краткой сводкой
     * @throws DateMalformedStringException
     */
    public function index(Request $request): View|JsonResponse
    {
        $flags = array_values($this->flagAdminRepo->list());

        $periodFrom = $request->get('from')
            ? new DateTimeImmutable($request->get('from'))
            : (new DateTimeImmutable())->modify('-7 days');
        $periodTo = $request->get('to')
            ? new DateTimeImmutable($request->get('to'))
            : new DateTimeImmutable();

        $summaries = [];
        foreach ($flags as $flag) {
            $summaries[$flag->name] = $this->statsService->getFlagSummary(
                $flag->name, $periodFrom, $periodTo
            );
        }

        return $this->respond(
            view: 'featureFlags::admin.statistics.index',
            data: [
                'flags' => $flags,
                'summaries' => $summaries,
                'periodFrom' => $periodFrom->format('Y-m-d'),
                'periodTo' => $periodTo->format('Y-m-d'),
            ],
            json: [
                'data' => array_map(
                    fn($flag) => [
                        'name' => $flag->name,
                        'description' => $flag->description,
                        'stats' => $summaries[$flag->name]?->toJson()
                    ],
                    $flags
                )
            ]
        );
    }

    /**
     * Детальная статистика по одному флагу
     * @throws DateMalformedStringException
     */
    public function show(string $flagName, Request $request): View|RedirectResponse|JsonResponse
    {
        $flag = $this->flagAdminRepo->findByName($flagName);
        if (!$flag) {
            return $this->respond(
                redirect: redirect()->route('featureFlags::index')->with('error', "Флаг '{$flagName}' не найден"),
                json: ['message' => "Flag '{$flagName}' not found"],
                statusCode: 404
            );
        }

        // Фильтры
        $filters = array_filter([
            'from' => $request->query('from'),
            'to' => $request->query('to'),
            'variant' => $request->query('variant'),
            'ip' => $request->query('ip'),
            'per_page' => $request->query('per_page'),
        ], fn($v) => $v !== null && trim((string)$v) !== '');

        $page = max(1, (int)$request->query('page', 1));
        $perPage = min(
            max(config('feature_flags.statistics.per_page', 50), 1),
            config('feature_flags.statistics.max_per_page', 200)
        );
        $perPage = min((int)$request->query('per_page', $perPage), config('feature_flags.statistics.max_per_page', 200));

        // Сводка
        $summary = $this->statsService->getFlagSummary(
            $flagName,
            $filters['from'] ? new DateTimeImmutable($filters['from']) : null,
            $filters['to'] ? new DateTimeImmutable($filters['to']) : null
        );

        // Записи + пагинация
        $result = $this->statsService->searchRecords($flagName, $filters, $page, $perPage);
        $records = $result['records'];
        $paginationMeta = $result['pagination'];

        // Группировка по хешу (только если не детальный режим)
        $grouped = $request->get('view_mode') !== 'detailed';
        $hashCounts = $grouped ? $this->statsService->getContextHashCounts($flagName, $filters) : [];

        // Пагинация + подготовка данных для шаблона
        $pagination = $this->preparePaginationData(
            baseUrl: route('featureFlags::statistics.show', $flagName),
            meta: $paginationMeta,
            filters: $filters
        );

        // График
        $chartData = $this->prepareChartData($summary->variantDistribution);

        $viewRecords = $this->prepareViewRecords($records, $hashCounts, $grouped);

        return $this->respond(
            view: 'featureFlags::admin.statistics.show',
            data: [
                'flag' => $flag,
                'summary' => $summary,
                'viewRecords' => $viewRecords,
                'filters' => $filters,
                'pagination' => $pagination,
                'chartData' => $chartData,
                'grouped' => $grouped,
            ],
            json: [
                'flag' => $flag->toArray(),
                'summary' => $summary->jsonSerialize(),
                'records' => array_map(fn($r) => $r->jsonSerialize(), $records), // Для API оставляем сырые
                'pagination' => $pagination,
                'chartData' => $chartData,
            ]
        );
    }

    /**
     * Подготовка данных для отображения пагинации в шаблоне
     */
    private function preparePaginationData(string $baseUrl, array $meta, array $filters): array
    {
        $currentPage = $meta['current_page'] ?? 1;
        $totalPages = $meta['total_pages'] ?? 1;

        // Фильтруем пустые значения и преобразуем даты в строки для URL
        $cleanFilters = array_filter(
            array_map(
                fn($v) => $v instanceof DateTimeImmutable ? $v->format('Y-m-d') : $v,
                $filters
            ),
            fn($v) => $v !== null && trim((string)$v) !== ''
        );

        // Базовый query string без page
        $baseQuery = http_build_query($cleanFilters);
        $queryString = $baseQuery ? '?' . $baseQuery . '&' : '?';

        // Генерируем массив страниц для отображения (с многоточием)
        $pages = $this->generatePaginationPages($currentPage, $totalPages);

        return [
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'has_prev' => $meta['has_prev'] ?? false,
            'has_next' => $meta['has_next'] ?? false,
            'total' => $meta['total'] ?? 0,
            'pages' => $pages, // [1, 2, '...', 10, 11, '...', 20]
            'urls' => [
                'prev' => $meta['has_prev'] ? $baseUrl . $queryString . 'page=' . ($currentPage - 1) : null,
                'next' => $meta['has_next'] ? $baseUrl . $queryString . 'page=' . ($currentPage + 1) : null,
                'base' => $baseUrl . $queryString,
            ],
        ];
    }

    /**
     * Генерация массива страниц с многоточием
     * Возвращает: [1, 2, '...', 8, 9, 10, '...', 20]
     */
    private function generatePaginationPages(int $currentPage, int $totalPages, int $delta = 1): array
    {
        if ($totalPages <= 1) {
            return [];
        }

        $pages = [];

        // Всегда показываем первую страницу
        $pages[] = 1;

        // Многоточие после первой, если нужно
        if ($currentPage - $delta > 2) {
            $pages[] = '...';
        }

        // Страницы вокруг текущей
        $start = max(2, $currentPage - $delta);
        $end = min($totalPages - 1, $currentPage + $delta);

        for ($i = $start; $i <= $end; $i++) {
            $pages[] = $i;
        }

        // Многоточие перед последней, если нужно
        if ($currentPage + $delta < $totalPages - 1) {
            $pages[] = '...';
        }

        // Всегда показываем последнюю страницу (если не первая)
        $pages[] = $totalPages;

        // Убираем дубликаты и сортируем (для случая, когда текущая рядом с краем)
        return array_values(array_unique($pages, SORT_REGULAR));
    }

    /**
     * Экспорт статистики в CSV
     */
    public function export(string $flagName, Request $request): void
    {
        $filters = [
            'from' => $request->get('from') ? new DateTimeImmutable($request->get('from')) : null,
            'to' => $request->get('to') ? new DateTimeImmutable($request->get('to')) : null,
            'variant' => $request->get('variant'),
        ];

        $records = $this->statsService->searchRecords(
            flagName: $flagName,
            filters: array_filter($filters),
            perPage: 10000
        );

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="flag_stats_' . $flagName . '_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['evaluated_at', 'variant', 'weight', 'result', 'ip', 'context_hash']);

        foreach ($records as $record) {
            fputcsv($output, [
                $record->evaluatedAt->format('Y-m-d H:i:s'),
                $record->variant ?? '',
                $record->weight ?? '',
                $record->result ? 'true' : 'false',
                $record->ip,
                $record->contextHash,
            ]);
        }
        fclose($output);
        exit;
    }

    /**
     * Универсальный ответ: либо View + Redirect, либо JSON (как в родительском контроллере)
     */
    private function respond(
        ?string           $view = null,
        array             $data = [],
        ?RedirectResponse $redirect = null,
        ?array            $json = null,
        int               $statusCode = 200
    ): View|RedirectResponse|JsonResponse
    {
        // Если запрос JSON (Accept header или ?api=1)
        if (request()->wantsJson() || request()->boolean('api')) {
            return new JsonResponse($json ?? [], $statusCode);
        }

        if ($redirect) {
            return $redirect;
        }

        return $view ? view($view, $data) : new JsonResponse($json ?? [], $statusCode);
    }

    /**
     * Подготовка записей для отображения:
     * 1. Группирует одинаковые хеши подряд (для аккордеона)
     * 2. Добавляет метаданные для шаблона (isGroupHeader, groupId, hashCount)
     */
    private function prepareViewRecords(array $records, array $hashCounts, bool $grouped): array
    {
        if (!$grouped) {
            return array_map(fn($r) => [
                'evaluatedAt' => $r->evaluatedAt,
                'variant' => $r->variant,
                'weight' => $r->weight,
                'matchedRule' => $r->matchedRule,
                'result' => $r->result,
                'ip' => $r->ip,
                'contextHash' => $r->contextHash,
                'context' => $r->context,
                'isGroupHeader' => true,
                'isGroupChild' => false,
                'groupId' => null,
                'hashCount' => 1,
            ], $records);
        }

        $groupedByHash = [];
        foreach ($records as $r) {
            $groupedByHash[$r->contextHash][] = $r;
        }

        uasort($groupedByHash, fn($a, $b) => $b[0]->evaluatedAt <=> $a[0]->evaluatedAt);

        $result = [];
        foreach ($groupedByHash as $hash => $groupRecords) {
            $count = (int)($hashCounts[$hash] ?? count($groupRecords));

            foreach ($groupRecords as $index => $r) {
                $result[] = [
                    'evaluatedAt' => $r->evaluatedAt,
                    'variant' => $r->variant,
                    'weight' => $r->weight,
                    'matchedRule' => $r->matchedRule,
                    'result' => $r->result,
                    'ip' => $r->ip,
                    'contextHash' => $hash,
                    'context' => $r->context,
                    'isGroupHeader' => $index === 0,
                    'isGroupChild' => $index > 0,
                    'groupId' => $hash,
                    'hashCount' => $count,
                ];
            }
        }

        return $result;
    }

    /**
     * Подготовка данных для Chart.js
     */
    private function prepareChartData(array $variantDist): array
    {
        return [
            'labels' => array_values(array_keys($variantDist)),
            'datasets' => [[
                'label' => 'Распределение вариантов',
                'data' => array_values(array_map(
                    fn($d) => is_numeric($d['percentage']) ? (float)$d['percentage'] : 0.0,
                    $variantDist
                )),
                'backgroundColor' => ['#36A2EB', '#FF6384', '#4BC0C0', '#FFCE56', '#9966FF'],
            ]]
        ];
    }
}
