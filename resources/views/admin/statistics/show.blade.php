@extends('featureFlags::layouts.manager')

@section('title', __('featureFlags::global.statistic_list') . ': ' . $flag->name)

@section('actions')
    <div id="actions">
        <a href="{{ route('featureFlags::statistics.index') }}" class="btn btn-info">
            <i class="fa fa-chart-bar"></i> @lang('featureFlags::global.statistic_list')
        </a>
        <a href="{{ route('featureFlags::statistics.export', $flag->name) }}?from={{ $filters['from'] ?? '' }}&to={{ $filters['to'] ?? '' }}"
           class="btn btn-success">
            <i class="fa fa-download"></i> @lang('featureFlags::global.export') CSV
        </a>
        <a href="{{ route('featureFlags::index') }}" class="btn btn-info">
            <i class="fa fa-list"></i> @lang('featureFlags::global.flag_list')
        </a>
        <a href="{{ route('featureFlags::edit', $flag->name) }}" class="btn btn-secondary">
            <i class="fa fa-edit"></i> @lang('featureFlags::global.edit_flag')
        </a>
        <a href="javascript:;" class="btn btn-secondary" onclick="location.reload();">
            <i class="fa fa-refresh"></i><span> @lang('featureFlags::global.refresh')</span>
        </a>
    </div>
@endsection

@section('content')
    <div class="tab-page" id="stats_tab">
        <h2 class="tab">
            <i class="fa fa-chart-bar"></i> @lang('featureFlags::global.statistic_list'): {{ $flag->name }}
        </h2>
        <script>tpModule.addTabPage(document.getElementById('stats_tab'));</script>

        <div class="container-fluid px-4 py-3">
            <!-- Сводка -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-muted">@lang('featureFlags::global.total_evaluation')</h5>
                            <p class="card-text display-6 fw-bold">{{ number_format($summary->totalEvaluations) }}</p>
                            <small class="text-muted">@lang('featureFlags::global.for_period')</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="card-title text-muted">@lang('featureFlags::global.unique_variants')</h5>
                            <p class="card-text display-6 fw-bold">{{ count($summary->variantDistribution) }}</p>
                            <small class="text-muted">A/B/C</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">@lang('featureFlags::global.filter')</h5>
                            <form method="GET" class="row g-2 align-items-end">
                                <div class="col-auto">
                                    <div class="input-group">
                                        <label class="form-label small">@lang('featureFlags::global.period')</label>
                                        <div class="input-group">
                                            <input type="date" name="from" class="form-control"
                                                   value="{{ is_object($filters['from'] ?? null) ? $filters['from']->format('Y-m-d') : ($filters['from'] ?? '') }}">
                                            <span class="input-group-text btn-sm">—</span>
                                            <input type="date" name="to" class="form-control"
                                                   value="{{ is_object($filters['to'] ?? null) ? $filters['to']->format('Y-m-d') : ($filters['to'] ?? '') }}">
                                        </div>

                                    </div>
                                </div>
                                <div class="col-auto">
                                    <label class="form-label small">@lang('featureFlags::global.variant')</label>
                                    <select name="variant" class="form-select">
                                        <option value="">@lang('featureFlags::global.all')</option>
                                        @foreach(array_keys($summary->variantDistribution) as $v)
                                            <option value="{{ $v }}" {{ $filters['variant']===$v?'selected':'' }}>{{ $v }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <label class="form-label small">@lang('featureFlags::global.on_page')</label>
                                    <select name="per_page" class="form-select" onchange="this.form.submit()">
                                        @foreach([25, 50, 100, 200] as $option)
                                            <option value="{{ $option }}"
                                                    {{ request()->query('per_page') == $option ? 'selected' : '' }}>
                                                {{ $option }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-primary" title="@lang('featureFlags::global.filter')"><i
                                                class="fa fa-filter"></i></button>
                                </div>
                                <div class="col-auto">
                                    <a href="{{ route('featureFlags::statistics.show', $flag->name) }}"
                                       class="btn btn-light" title="@lang('featureFlags::global.reset_filters')">
                                        <i class="fa fa-close fa-fw"></i>
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- График распределения -->
            @if($summary->variantDistribution && !empty($chartData['datasets'][0]['data'] ?? []))
                <div class="row mb-4">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header bg-primary text-white">@lang('featureFlags::global.distribution_variants')</div>
                            <div class="card-body">
                                <!-- Контейнер с фиксированной высотой для Chart.js -->
                                <div style="position: relative; height: 250px; min-height: 250px;">
                                    <canvas id="variantChart"
                                            style="display: block; width: 100%; height: 100%;"></canvas>
                                </div>
                                @if(empty($chartData['datasets'][0]['data'] ?? []))
                                    <div class="text-center text-muted py-4">
                                        <i class="fa fa-chart-pie fa-2x mb-2"></i>
                                        <p class="mb-0">@lang('featureFlags::global.no_chart_data')</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header bg-primary text-white">@lang('featureFlags::global.details')</div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    @foreach($summary->variantDistribution as $variant => $data)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <strong>{{ $variant }}</strong>
                                            {{ $data['count'] }} ({{ $data['percentage'] }}%)
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Таблица последних записей -->
            <div class="mb-3 text-end">
                <div class="btn-group btn-group-sm" role="group">
                    <a href="{{ route('featureFlags::statistics.show', $flag->name) }}?{{ http_build_query(array_merge($filters, ['view_mode' => 'grouped', 'page' => 1])) }}"
                       class="btn {{ $grouped ? 'btn-primary' : 'btn-outline-primary' }}">
                        <i class="fa fa-layer-group"></i> @lang('featureFlags::global.with_grouping')
                    </a>
                    <a href="{{ route('featureFlags::statistics.show', $flag->name) }}?{{ http_build_query(array_merge($filters, ['view_mode' => 'detailed', 'page' => 1])) }}"
                       class="btn {{ !$grouped ? 'btn-primary' : 'btn-outline-primary' }}">
                        <i class="fa fa-list"></i> @lang('featureFlags::global.detailed')
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><span>@lang('featureFlags::global.recent_evaluation')</span></div>
                <div class="card-block">
                    <div class="widget-stage">
                        <div class="table-responsive">
                            <table class="table data">
                                <thead class="table-light">
                                <tr>
                                    <th>@lang('featureFlags::global.time')</th>
                                    <th>@lang('featureFlags::global.variant')</th>
                                    <th>@lang('featureFlags::global.weight')</th>
                                    <th>@lang('featureFlags::global.rule_that_worked')</th>
                                    <th>@lang('featureFlags::global.result')</th>
                                    <th>IP</th>
                                    <th>@lang('featureFlags::global.context')</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($viewRecords as $row)
                                    @if($row['isGroupHeader'])
                                        <tr class="group-header" data-group-id="{{ $row['groupId'] }}">
                                            <td>
                                                {{ $row['evaluatedAt']->format('H:i:s d.m.Y') }}
                                            </td>
                                            <td>@if($row['variant']) <span class="badge bg-info text-white">{{ $row['variant'] }}</span> @else <span class="text-muted">—</span> @endif</td>
                                            <td>{{ $row['weight'] !== null ? number_format($row['weight']*100, 1).'%' : '—' }}</td>
                                            <td>
                                                @if($row['matchedRule'])
                                                    <code class="small text-muted" title="@lang('featureFlags::global.rule_that_worked')">
                                                        {{ Str::limit($row['matchedRule'], 30) }}
                                                    </code>
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                            <td><span class="badge bg-{{ $row['result'] ? 'success' : 'danger' }} text-white"><i class="fa {{ $row['result'] ? 'fa-check' : 'fa-close' }}"></i></span></td>
                                            <td>
                                                <small>{{ $row['ip'] }}</small>
                                                @if($row['groupId'])<br><code class="small text-muted" title="context_hash">{{ substr($row['contextHash'], 0, 8) }}…</code>@endif
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn border-0" onclick="toggleCtxBlock(this, 'ctx-{{ $loop->iteration }}')"><i class="fa fa-eye me-1"></i></button>
                                                    @if($row['groupId'] && $row['hashCount'] > 1)
                                                        <button type="button" class="btn border-0 toggle-group" data-target="{{ $row['groupId'] }}">
                                                        <span class="badge">
                                                            <i class="fa fa-users"></i> {{ $row['hashCount'] }}
                                                        </span>
                                                            <i class="fa fa-chevron-down ms-1 group-icon"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                                <div class="collapse mt-1" id="ctx-{{ $loop->iteration }}">
                                                    <pre class="small bg-light p-2 rounded border" style="max-height: 200px; overflow-y: auto;">
                                                        <code>{{ json_encode($row['context'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) }}</code>
                                                    </pre>
                                                </div>
                                            </td>
                                        </tr>

                                    @elseif($row['isGroupChild'])
                                        <tr class="group-child table-light" data-group-id="{{ $row['groupId'] }}" style="display: none;">
                                            <td>{{ $row['evaluatedAt']->format('H:i:s d.m.Y') }} <small class="text-muted ms-2">↪ @lang('featureFlags::global.rerun')</small></td>
                                            <td>@if($row['variant']) <span class="badge bg-info text-white">{{ $row['variant'] }}</span> @else <span class="text-muted">—</span> @endif</td>
                                            <td>{{ $row['weight'] !== null ? number_format($row['weight']*100, 1).'%' : '—' }}</td>
                                            <td>
                                                @if($row['matchedRule'])
                                                    <code class="small text-muted" title="@lang('featureFlags::global.rule_that_worked')">
                                                        {{ Str::limit($row['matchedRule'], 30) }}
                                                    </code>
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                            <td><span class="badge bg-{{ $row['result'] ? 'success' : 'danger' }} text-white"><i class="fa {{ $row['result'] ? 'fa-check' : 'fa-close' }}"></i></span></td>
                                            <td><small>{{ $row['ip'] }}</small></td>
                                            <td>
                                                <button type="button" class="btn border-0" onclick="toggleCtxBlock(this, 'ctx-{{ $loop->iteration }}')"><i class="fa fa-eye me-1"></i></button>
                                                <div class="collapse mt-1" id="ctx-{{ $loop->iteration }}">
                                        <pre class="small bg-light p-2 rounded border" style="max-height: 200px; overflow-y: auto;">
                                            <code>{{ json_encode($row['context'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) }}</code>
                                        </pre>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr><td colspan="6" class="text-center py-4 text-muted">@lang('featureFlags::global.no_records_selected_period')</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                @if($pagination['total_pages'] > 1)
                    <div class="card-footer bg-white">
                        @include('featureFlags::partials.pagination', ['pagination' => $pagination])
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0"></script>
    <script>
        const canvas = document.getElementById('variantChart');
        const data = @json($chartData);

        if (canvas && typeof Chart !== 'undefined' && data?.datasets?.[0]?.data?.length) {
            // Принудительно задаём размеры канвасу
            canvas.style.display = 'block';
            canvas.width = canvas.offsetWidth || 400;
            canvas.height = canvas.offsetHeight || 250;

            new Chart(canvas, {
                type: 'pie',
                data: data,
                options: {
                    responsive: false, // Отключил responsive
                    plugins: {
                        legend: {position: 'bottom'}
                    }
                }
            });
        } else {
            console.error('[Chart Debug] Init skipped:', {
                canvas: !!canvas,
                chartLib: typeof Chart !== 'undefined',
                hasData: !!(data?.datasets?.[0]?.data?.length)
            });
        }
    </script>
    <script>
        // Глобальная функция
        window.toggleCtxBlock = function (btn, id) {
            const el = document.getElementById(id);
            if (!el) return;

            const isHidden = !el.classList.contains('show');

            // 1. Тоглим класс Bootstrap (если BS JS загружен)
            el.classList.toggle('show');

            // 2. Фоллбэк для сред без Bootstrap JS
            el.style.display = isHidden ? 'block' : 'none';

            // 3. Обновляем иконку и текст кнопки
            const icon = btn.querySelector('i');
            const text = btn.querySelector('span');
            if (icon) icon.className = isHidden ? 'fa fa-eye-slash me-1' : 'fa fa-eye me-1';
            if (text) text.textContent = isHidden ? @lang('featureFlags::global.hide') : @lang('featureFlags::global.show');

            // 4. ARIA для доступности
            btn.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
        };
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.toggle-group').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.dataset.target;
                    const children = document.querySelectorAll('tr.group-child[data-group-id="' + targetId + '"]');
                    const icon = this.querySelector('.group-icon');
                    const badge = this.querySelector('.badge');

                    if (children.length === 0) return;

                    const isHidden = children[0].style.display === 'none';
                    children.forEach(function(row) {
                        row.style.display = isHidden ? '' : 'none';
                    });

                    if (icon) icon.className = 'fa ' + (isHidden ? 'fa-chevron-up' : 'fa-chevron-down') + ' ms-1 group-icon';
                    if (badge) {
                        const count = children.length + 1;
                        badge.innerHTML = '<i class="fa fa-users"></i> ' + (isHidden ? 'Скрыть' : 'Показать') + ' (' + count + ')';
                    }
                });
            });
        });
    </script>
@endpush
