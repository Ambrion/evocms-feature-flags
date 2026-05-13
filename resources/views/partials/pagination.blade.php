{{--
    Параметры:
    - $pagination: array с current_page, total_pages, has_prev, has_next, pages, urls, total
--}}
@if(($pagination['total_pages'] ?? 1) > 1)
    <nav aria-label="@lang('featureFlags::global.pagination_records')" class="mt-3">
        <ul class="pagination pagination-sm justify-content-center mb-0">

            {{-- Кнопка "Назад" --}}
            <li class="page-item {{ !$pagination['has_prev'] ? 'disabled' : '' }}">
                <a class="page-link"
                   href="{{ $pagination['urls']['prev'] ?? '#' }}"
                   aria-label="@lang('featureFlags::global.previous')">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>

            {{-- Номера страниц --}}
            @foreach($pagination['pages'] ?? [] as $page)
                @if($page === '...')
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                @else
                    <li class="page-item {{ $page === $pagination['current_page'] ? 'active' : '' }}">
                        <a class="page-link" href="{{ $pagination['urls']['base'] }}page={{ $page }}">
                            {{ $page }}
                        </a>
                    </li>
                @endif
            @endforeach

            {{-- Кнопка "Вперёд" --}}
            <li class="page-item {{ !$pagination['has_next'] ? 'disabled' : '' }}">
                <a class="page-link"
                   href="{{ $pagination['urls']['next'] ?? '#' }}"
                   aria-label="@lang('featureFlags::global.next')">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>

        {{-- Инфо о записях --}}
        <small class="text-muted d-block text-center mt-2">
            @lang('featureFlags::global.page') {{ $pagination['current_page'] }} @lang('featureFlags::global.page_of') {{ $pagination['total_pages'] }}
            (@lang('featureFlags::global.total') {{ $pagination['total'] }} @lang('featureFlags::global.records'))
        </small>
    </nav>
@endif
