@extends('featureFlags::layouts.manager')

@section('title', __('featureFlags::global.flag_statistics'))

@section('actions')
    <div id="actions">
        <a href="{{ route('featureFlags::index') }}" class="btn btn-info">
            <i class="fa fa-list"></i> @lang('featureFlags::global.flag_list')
        </a>
        <a href="javascript:;" class="btn btn-secondary" onclick="location.reload();">
            <i class="fa fa-refresh"></i><span> @lang('featureFlags::global.refresh')</span>
        </a>
    </div>
@endsection

@section('content')
    <div class="tab-page" id="stats_list_tab">
        <h2 class="tab">
            <i class="fa fa-chart-bar"></i> @lang('featureFlags::global.flag_statistics')
        </h2>
        <script>tpModule.addTabPage(document.getElementById('stats_list_tab'));</script>

        <!-- Фильтр периода -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-auto">
                        <label class="form-label">@lang('featureFlags::global.period')</label>
                        <div class="input-group">
                            <input type="date" name="from" class="form-control"
                                   value="{{ is_object($periodFrom ?? null) ? $periodFrom->format('Y-m-d') : ($periodFrom ?? '') }}">
                            <span class="input-group-text btn-sm">—</span>
                            <input type="date" name="to" class="form-control"
                                   value="{{ is_object($periodTo ?? null) ? $periodTo->format('Y-m-d') : ($periodTo ?? '') }}">
                        </div>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">@lang('featureFlags::global.apply')</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Таблица флагов -->
        <div class="card">
            <div class="card-header">
                @lang('featureFlags::global.flag_statistics')
            </div>
            <div class="card-block">
                <div class="widget-stage">
                    <div class="table-responsive">
                        <table class="table data">
                            <thead>
                            <tr>
                                <th>@lang('featureFlags::global.flag')</th>
                                <th>@lang('featureFlags::global.item_description')</th>
                                <th class="text-end">@lang('featureFlags::global.evaluation')</th>
                                <th class="text-end">@lang('featureFlags::global.variants')</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($flags as $flag)
                                @php $summary = $summaries[$flag->name] ?? null; @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $flag->name }}</strong>
                                        @if(!$flag->is_active)
                                            <span class="badge bg-secondary ms-1">@lang('featureFlags::global.inactive')</span>
                                        @endif
                                    </td>
                                    <td><small class="text-muted">{{ Str::limit($flag->description, 60) }}</small>
                                    </td>
                                    <td class="text-end">
                                        @if($summary)
                                            {{ number_format($summary->totalEvaluations) }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($summary?->variantDistribution)
                                            @foreach($summary->variantDistribution as $v => $d)
                                                {{ $v }}: {{ $d['percentage'] }}%
                                            @endforeach
                                        @else
                                            <span class="text-muted">@lang('featureFlags::global.no_data')</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('featureFlags::statistics.show', $flag->name) }}?from={{ $periodFrom }}&to={{ $periodTo }}" class="btn border-0"  title="Статистика">
                                            <i class="fa fa-chart-bar"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">@lang('featureFlags::global.no_active_flags')</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
