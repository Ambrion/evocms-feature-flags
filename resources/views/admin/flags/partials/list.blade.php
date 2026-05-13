<div class="card">
    <div class="card-header">
        <i class="fa fa-list"></i> @lang('featureFlags::global.flag_list')
    </div>
    <div class="card-block">
        <div class="widget-stage">
            <div class="table-responsive">
                <table class="table data">
                    <thead>
                    <tr>
                        <th>@lang('featureFlags::global.item_name')</th>
                        <th>@lang('featureFlags::global.item_description')</th>
                        <th>@lang('featureFlags::global.total_rules')</th>
                        <th style="width: 1%">@lang('featureFlags::global.by_default')</th>
                        <th style="width: 1%">@lang('featureFlags::global.status')</th>
                        <th style="width: 1%; text-align: center">@lang('featureFlags::global.action')</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($flags as $flag)
                        <tr>
                            <td class="text-nowrap">
                                <code class="text-primary fw-bold fs-6">{{ $flag->name }}</code>
                            </td>
                            <td>
                                {{ Str::limit($flag->description ?: '—', 60) }}
                            </td>
                            <td>
                                @if($flag->rules)
                                    {{ count($flag->rules) }}
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $flag->default_value ? 'success' : 'secondary' }} text-white">
                                    {{ is_bool($flag->default_value) ? ($flag->default_value ? '✓ ' .  __('featureFlags::global.yes') : '✗ ' . __('featureFlags::global.no')) : $flag->default_value }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($flag->is_active)
                                    <span class="badge bg-success text-white fs-5"><i class="fa fa-check"></i></span>
                                @else
                                    <span class="badge bg-danger text-white fs-5"><i class="fa fa-times"></i></span>
                                @endif
                            </td>

                            <td class="actions">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('featureFlags::statistics.show', $flag->name) }}" class="btn border-0"  title="@lang('featureFlags::global.statistic_list')">
                                        <i class="fa fa-chart-bar"></i>
                                    </a>
                                    <a href="{{ route('featureFlags::edit', $flag->name) }}"
                                       class="btn border-0" title="@lang('featureFlags::global.edit_item')">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('featureFlags::destroy', $flag->name) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('@lang('featureFlags::global.delete_flag') \'{{ $flag->name }}\'?')">
                                        @csrf
                                        <button type="submit" class="btn border-0" title="@lang('featureFlags::global.delete')">
                                            <i class="fa fa-trash fa-fw"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fa fa-flag fa-3x mb-3 d-block opacity-25"></i>
                                <p class="fs-5">@lang('featureFlags::global.no_flag_items')</p>
                                <a href="{{ route('featureFlags::create') }}" class="btn btn-primary btn-lg mt-2">
                                    <i class="fa fa-plus me-1"></i> @lang('featureFlags::global.create_first_flag')
                                </a>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
