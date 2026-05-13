@extends('featureFlags::layouts.manager')

@section('title', __('featureFlags::global.main_caption'))

@section('actions')
    <div id="actions">
        <a href="{{ route('featureFlags::create') }}" class="btn btn-success">
            <i class="fa fa-plus"></i> @lang('featureFlags::global.add_flag')
        </a>
        <a href="{{ route('featureFlags::statistics.index') }}" class="btn btn-info">
            <i class="fa fa-chart-bar"></i> @lang('featureFlags::global.statistic_list')
        </a>
        <a href="javascript:;" class="btn" onclick="location.reload();">
            <i class="fa fa-refresh"></i><span> @lang('featureFlags::global.refresh')</span>
        </a>
    </div>
@endsection

@section('content')
    <div class="tab-page" id="tabFeatureFlagsList">
        <h2 class="tab">
            <i class="fa fa-flag"></i> @lang('featureFlags::global.flag_list')
            <span class="badge bg-secondary ms-2 fs-6 py-1 px-2 text-white">{{ count($flags ?? []) }}</span>
        </h2>
        <script type="text/javascript">
            tpModule.addTabPage(document.getElementById('tabFeatureFlagsList'));
        </script>
        @include('featureFlags::admin.flags.partials.list')
    </div>

    <div class="tab-page" id="tabFeatureFlagsAbout">
        <h2 class="tab">
            <i class="fa fa-info"></i> @lang('featureFlags::global.about_module')
        </h2>
        <script type="text/javascript">
            tpModule.addTabPage(document.getElementById('tabFeatureFlagsAbout'));
        </script>
        @include('featureFlags::admin.about.index')
    </div>
@endsection
