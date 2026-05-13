@extends('featureFlags::layouts.manager')

@section('title', $flag ? __('featureFlags::global.edit_flag') : __('featureFlags::global.create_flag'))

@section('actions')
    <div id="actions">
        <a href="{{ route('featureFlags::index') }}" class="btn btn-info">
            <i class="fa fa-list"></i> @lang('featureFlags::global.flag_list')
        </a>
        <a href="{{ route('featureFlags::statistics.show', $flag->name) }}" class="btn btn-secondary">
            <i class="fa fa-chart-bar"></i> @lang('featureFlags::global.flag_statistic')
        </a>
        <a href="javascript:;" class="btn btn-secondary" onclick="location.reload();">
            <i class="fa fa-refresh"></i><span> @lang('featureFlags::global.refresh')</span>
        </a>
    </div>
@endsection

@section('content')
    <div class="tab-page" id="tab_feature_flags_edit">
        <h2 class="tab">
            <i class="fa fa-building"></i> {{ $flag ? __('featureFlags::global.edit_flag') : __('featureFlags::global.create_flag') }} {{ $flag->name }}
        </h2>
        <script type="text/javascript">
            tpModule.addTabPage(document.getElementById('tab_feature_flags_edit'));
        </script>

        <form method="POST" action="{{ $flag ? route('featureFlags::update', $flag->name) : route('featureFlags::store') }}">
            <div class="container-fluid px-4">
                <div class="row g-4 mb-5">
                    <!-- Левая колонка: Основные параметры -->
                    <div class="col-lg-5">
                        <div class="card h-100">
                            <div class="card-header bg-primary text-white py-3">
                                <h5 class="mb-0 fs-5"><i class="fa fa-cog"></i> @lang('featureFlags::global.params')</h5>
                            </div>
                            <div class="card-body p-4">
                                @csrf

                                @if(!$flag)
                                    <div class="mb-4">
                                        <label for="name" class="form-label fw-bold fs-6">
                                            @lang('featureFlags::global.item_name') <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="name" name="name"
                                               class="form-control @error('name') is-invalid @enderror"
                                               value="{{ old('name') }}"
                                               placeholder="show_new_year_banner"
                                               pattern="^[a-z][a-z0-9_]*$"
                                               required>
                                        @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>@enderror
                                        <div class="form-text fs-6 mt-2">
                                            <i class="fa fa-info-circle me-1"></i>
                                            @lang('featureFlags::global.name_validation_help_text')
                                        </div>
                                    </div>
                                @endif

                                <div class="mb-4">
                                    <label class="form-label fw-bold fs-6">@lang('featureFlags::global.default_value'):</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <!-- Кнопки быстрого выбора -->
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-outline-success btn-sm quick-default"
                                                    data-val="true" title="@lang('featureFlags::global.set') true (boolean)">
                                                <i class="fa fa-check"></i> @lang('featureFlags::global.on')
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm quick-default"
                                                    data-val="false" title="@lang('featureFlags::global.set') false (boolean)">
                                                <i class="fa fa-times"></i> @lang('featureFlags::global.off')
                                            </button>
                                        </div>

                                        <!-- Поле для кастомных значений (A, B, C, 123...) -->
                                        <input type="text" id="default_value_input" name="default_value_raw"
                                               class="form-control form-control-sm" style="width: 150px;"
                                               placeholder="A, B, C, 123..."
                                               value="{{
                                                   old('default_value_raw',
                                                       $flag && $flag->default_value === true ? 'true' :
                                                       ($flag && $flag->default_value === false ? 'false' :
                                                       ($flag && $flag->default_value === null ? '' :
                                                       ($flag ? $flag->default_value : 'false'))))
                                               }}"
                                               title="@lang('featureFlags::global.default_value_input_help_text')">

                                        <!-- Скрытое поле для отправки (заполняется через JS) -->
                                        <input type="hidden" name="default_value" id="default_value_hidden"
                                               value="{{ old('default_value', $flag ? json_encode($flag->default_value) : 'false') }}">
                                    </div>
                                    <div class="form-text fs-6 mt-2">
                                        <i class="fa fa-info-circle me-1"></i>
                                        <code>true</code>/<code>false</code> — @lang('featureFlags::global.true_false_help_text'),
                                        <code>"A"</code>/<code>"B"</code> — @lang('featureFlags::global.a_b_help_text')
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold fs-6">@lang('featureFlags::global.status'):</label>
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" class="form-check-input" id="is_active"
                                               name="is_active" value="1"
                                                {{ old('is_active', $flag->is_active ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label fs-6" for="is_active">
                                            <span class="text-success fw-bold">@lang('featureFlags::global.active_flag')</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-bold fs-6">@lang('featureFlags::global.statistic_list'):</label>
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="log_statistics" value="0">
                                        <input type="checkbox" class="form-check-input" id="log_statistics"
                                               name="log_statistics" value="1"
                                                {{ old('log_statistics', $flag->log_statistics ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label fs-6" for="log_statistics">
                                            <span class="text-info fw-bold">@lang('featureFlags::global.record_usage_statistics')</span>
                                        </label>
                                    </div>
                                    <div class="form-text fs-6 mt-2">
                                        <i class="fa fa-info-circle me-1"></i>
                                        @lang('featureFlags::global.record_usage_statistics_help_text').
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <label for="description" class="form-label fw-bold fs-6">@lang('featureFlags::global.item_description')</label>
                                    <textarea id="description" name="description"
                                              class="form-control @error('description') is-invalid @enderror"
                                              rows="4" maxlength="500"
                                              placeholder="@lang('featureFlags::global.description_help_text')">{{ old('description', $flag->description ?? '') }}</textarea>
                                    @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <div class="form-text fs-6 mt-2">
                                        <span id="descCounter">0</span>/500 @lang('featureFlags::global.symbols')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Правая колонка: Правила -->
                    <div class="col-lg-7">
                        <div class="card h-100">
                            <div class="card-header bg-success text-white py-3">
                                <h5 class="mb-0 fs-5"><i class="fa fa-sliders-h"></i> @lang('featureFlags::global.activation_rules')</h5>
                            </div>
                            <div class="card-body p-4">
                                <!-- Переключатель режимов -->
                                <div class="btn-group mb-3" role="group">
                                    <button type="button" id="toggle-builder"
                                            class="btn btn-outline-primary active">
                                        <i class="fa fa-puzzle-piece me-2"></i> @lang('featureFlags::global.builder')
                                    </button>
                                    <button type="button" id="toggle-raw" class="btn btn-outline-secondary">
                                        <i class="fa fa-code me-2"></i> Raw JSON
                                    </button>
                                </div>

                                <!-- Конструктор правил -->
                                <div id="rules-builder">
                                    <div id="rules-list" class="mb-3"></div>
                                    <button type="button" id="add-rule"
                                            class="btn btn-outline-secondary w-100 py-2">
                                        <i class="fa fa-plus me-2"></i> @lang('featureFlags::global.add_rule')
                                    </button>
                                </div>

                                <!-- Raw JSON -->
                                <div id="rules-raw" class="d-none">
                                    <textarea id="rules-raw-textarea" class="form-control font-monospace fs-6" rows="10"
                                      placeholder='[{"condition":"user_role=manager","value":true}]'></textarea>
                                </div>

                                {{-- Скрытое поле --}}
                                <textarea name="rules" id="rules-hidden" class="d-none"
                                          required>{{ old('rules', is_array($flag->rules ?? null) ? json_encode($flag->rules, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : ($flag->rules ?: '[]')) }}</textarea>

                                @error('rules')
                                <div class="invalid-feedback d-block mt-2">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Footer с кнопками -->
                            <div class="card-footer bg-white text-end py-3">
                                <button type="submit" class="btn btn-success">
                                    <i class="fa fa-save"></i> {{ $flag ? __('featureFlags::global.save') : __('featureFlags::global.create') }}
                                </button>
                                <a href="{{ route('featureFlags::index') }}" class="btn btn-secondary">
                                    <i class="fa fa-times"></i> @lang('featureFlags::global.cancel')
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card h-100">
                            <div class="card-header py-3">
                                <h5 class="mb-0 fs-5"><i class="fa fa-sliders-h"></i> @lang('featureFlags::global.example_conditions')</h5>
                            </div>
                            <div class="card-body p-4">
                                <div class="widget-stage">
                                    <div class="table-responsive">
                                        <table class="table data">
                                            <thead>
                                            <tr>
                                                <th>@lang('featureFlags::global.condition')</th>
                                                <th>@lang('featureFlags::global.item_description')</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="text-nowrap">
                                                        <code>user_role=manager</code> @lang('featureFlags::global.or') <code>user_role IN (manager,user)</code>
                                                    </td>
                                                    <td>
                                                        @lang('featureFlags::global.user_role_help_text').
                                                        <strong>@lang('featureFlags::global.for_example'):</strong> @lang('featureFlags::global.user_role_help_text_example').
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-nowrap">
                                                        <code>category=electronics</code> @lang('featureFlags::global.or') <code>category IN (electronics,clothing)</code>
                                                    </td>
                                                    <td>
                                                        @lang('featureFlags::global.category_help_text').
                                                        <strong>@lang('featureFlags::global.for_example'):</strong> @lang('featureFlags::global.category_help_text_example').
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-nowrap">
                                                        <code>target_id=10</code> @lang('featureFlags::global.or') <code>target_id IN (1,5)</code>
                                                    </td>
                                                    <td>
                                                        @lang('featureFlags::global.target_id_help_text').
                                                        <strong>@lang('featureFlags::global.for_example'):</strong> @lang('featureFlags::global.target_id_help_text_example').
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-nowrap">
                                                        <code>current_date BETWEEN 12-01 AND 12-31</code>
                                                    </td>
                                                    <td>
                                                        @lang('featureFlags::global.current_date_help_text').
                                                        <strong>@lang('featureFlags::global.for_example'):</strong> @lang('featureFlags::global.current_date_help_text_example').
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-nowrap">
                                                        <code>user_hash PERCENTAGE 20</code>
                                                    </td>
                                                    <td>
                                                        @lang('featureFlags::global.user_hash_help_text').
                                                        <strong>@lang('featureFlags::global.for_example'):</strong> @lang('featureFlags::global.user_hash_help_text_example').
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="text-nowrap">
                                                        <code>value: true/false/"A"/"B"/123</code>
                                                    </td>
                                                    <td>
                                                        <strong>@lang('featureFlags::global.value_format'):</strong>
                                                        <ul class="mb-0 small">
                                                            <li><code>true</code>/<code>false</code> — @lang('featureFlags::global.bool_text') (@lang('featureFlags::global.true_false_help_text'))</li>
                                                            <li><code>"A"</code>, <code>"B"</code> — @lang('featureFlags::global.string_text') (@lang('featureFlags::global.a_b_help_text'))</li>
                                                            <li><code>123</code> — @lang('featureFlags::global.number_text') (@lang('featureFlags::global.for_weights_priorities_help_text'))</li>
                                                        </ul>
                                                        💡 <em>@lang('featureFlags::global.value_format_help_text')</em>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>


    @push('scripts')
        <script>
            // Счётчик символов в описании
            document.getElementById('description')?.addEventListener('input', function () {
                document.getElementById('descCounter').textContent = this.value.length;
            });

            // JS конструктора правил
            document.addEventListener('DOMContentLoaded', function () {
                const list = document.getElementById('rules-list');
                const addBtn = document.getElementById('add-rule');
                const hiddenInput = document.getElementById('rules-hidden');
                const rawTextarea = document.getElementById('rules-raw-textarea');
                const builderDiv = document.getElementById('rules-builder');
                const rawDiv = document.getElementById('rules-raw');
                const toggleBuilder = document.getElementById('toggle-builder');
                const toggleRaw = document.getElementById('toggle-raw');

                let rules = [];
                try {
                    rules = JSON.parse(hiddenInput?.value || '[]');
                } catch (e) {
                    console.warn('⚠️ Ошибка парсинга правил из hiddenInput:', e);
                    rules = [];
                }

                /**
                 * Синхронизация правил в скрытое поле + визуальная обратная связь
                 */
                function syncToHidden() {
                    const currentRules = [];

                    document.querySelectorAll('.rule-row').forEach(row => {
                        const cond = row.querySelector('.rule-condition')?.value.trim() || '';
                        const rawValue = row.querySelector('.rule-value')?.value.trim() ?? '';

                        if (!cond) return;

                        // Умный парсинг значения (порядок проверок ВАЖЕН!)
                        let parsedValue;
                        const lowerVal = rawValue.toLowerCase();

                        if (lowerVal === 'true') {
                            parsedValue = true;
                        } else if (lowerVal === 'false') {
                            parsedValue = false;
                        } else if (rawValue === '' || lowerVal === 'null') {
                            parsedValue = null;
                        } else if (/^-?\d+$/.test(rawValue)) {
                            parsedValue = parseInt(rawValue, 10);
                        } else if (/^-?\d+\.\d+$/.test(rawValue)) {
                            parsedValue = parseFloat(rawValue);
                        } else {
                            // Строка: убираем лишние кавычки, если пользователь их ввёл
                            parsedValue = rawValue.replace(/^["']|["']$/g, '');
                        }

                        // Визуальная обратная связь (ОДИН проход — без дублирования!)
                        const valueInput = row.querySelector('.rule-value');
                        if (valueInput) {
                            // Сброс классов и стилей
                            valueInput.classList.remove('is-valid', 'is-invalid', 'text-primary', 'text-success', 'fw-bold');
                            valueInput.style.borderColor = '';
                            valueInput.style.fontFamily = '';

                            if (parsedValue === true || parsedValue === false) {
                                // Boolean: синий, моноширинный, жирный
                                valueInput.style.borderColor = '#0d6efd';
                                valueInput.classList.add('text-primary', 'fw-bold');
                                valueInput.style.fontFamily = 'monospace';
                                valueInput.title = `Тип: boolean (${parsedValue})`;
                            } else if (typeof parsedValue === 'number') {
                                // Number: зелёный, моноширинный
                                valueInput.style.borderColor = '#198754';
                                valueInput.classList.add('text-success');
                                valueInput.style.fontFamily = 'monospace';
                                valueInput.title = `Тип: number (${parsedValue})`;
                            } else {
                                // String: зелёная галочка валидации, обычный шрифт
                                valueInput.classList.add('is-valid');
                                valueInput.title = `Тип: string ("${parsedValue}")`;
                            }
                        }

                        currentRules.push({ condition: cond, value: parsedValue });
                    });

                    // Сохраняем в hidden input и raw textarea
                    hiddenInput.value = JSON.stringify(currentRules, null, 2);
                    if (rawTextarea) rawTextarea.value = hiddenInput.value;
                }

                /**
                 * Отрисовка правил из массива
                 */
                function renderRules() {
                    if (!list) return;
                    list.innerHTML = '';

                    rules.forEach((rule, index) => {
                        addRuleRow(rule.condition || '', rule.value);
                    });

                    syncToHidden();
                }

                /**
                 * Добавление строки правила в конструктор
                 */
                function addRuleRow(condition = '', value = true) {
                    // Надёжное приведение к строке для отображения
                    let displayValue;
                    if (value === true) {
                        displayValue = 'true';
                    } else if (value === false) {
                        displayValue = 'false';
                    } else if (value === null || value === undefined) {
                        displayValue = '';
                    } else {
                        displayValue = String(value);
                    }

                    const row = document.createElement('div');
                    row.className = 'rule-row bg-light border rounded p-3 mb-3 d-flex gap-2 align-items-center fs-6';
                    row.innerHTML = `
                <input type="text" class="form-control rule-condition flex-grow-1"
                       placeholder="e.g., user_hash PERCENTAGE 33"
                       value="${(condition || '').replace(/"/g, '&quot;')}">

                <!-- Группа быстрого выбора true/false + поле для кастомных значений -->
                <div class="d-flex align-items-center gap-1 p-1 bg-white border rounded">
                    <button type="button" class="btn btn-sm btn-outline-success quick-val"
                            data-val="true" title="Установить true (boolean)">
                        <i class="fa fa-check"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger quick-val"
                            data-val="false" title="Установить false (boolean)">
                        <i class="fa fa-times"></i>
                    </button>
                    <input type="text" class="form-control form-control-sm rule-value border-0 ps-1"
                           style="width: 120px; min-width: 100px;"
                           placeholder="A, B, C, 123..."
                           value="${(displayValue || '').replace(/"/g, '&quot;')}"
                           title="Введите своё значение или используйте кнопки слева">
                </div>

                <button type="button" class="btn btn-outline-danger remove-rule px-3" title="Удалить правило">
                    <i class="fa fa-trash"></i>
                </button>
            `;
                    list.appendChild(row);
                }

                /**
                 * Переключение режимов: Конструктор / Raw JSON
                 */
                function setMode(mode) {
                    if (mode === 'builder') {
                        builderDiv.classList.remove('d-none');
                        rawDiv.classList.add('d-none');
                        toggleBuilder.classList.add('active', 'btn-outline-primary');
                        toggleRaw.classList.remove('active', 'btn-outline-secondary');
                        try {
                            rules = JSON.parse(rawTextarea?.value || '[]');
                        } catch (e) {
                            console.warn('⚠️ Ошибка парсинга JSON из raw-режима:', e);
                            rules = [];
                        }
                        renderRules();
                    } else {
                        builderDiv.classList.add('d-none');
                        rawDiv.classList.remove('d-none');
                        toggleRaw.classList.add('active', 'btn-outline-secondary');
                        toggleBuilder.classList.remove('active', 'btn-outline-primary');
                        if (rawTextarea) rawTextarea.value = hiddenInput.value || '[]';
                    }
                }

                // Обработчики событий

                // Добавление нового правила
                addBtn?.addEventListener('click', () => {
                    addRuleRow();
                    syncToHidden();
                });

                // Удаление правила (делегирование)
                list?.addEventListener('click', e => {
                    if (e.target.closest('.remove-rule')) {
                        e.target.closest('.rule-row').remove();
                        syncToHidden();
                    }
                });

                // Быстрый выбор true/false (делегирование)
                document.addEventListener('click', function(e) {
                    const btn = e.target.closest('.quick-val');
                    if (!btn) return;
                    e.preventDefault();
                    const row = btn.closest('.rule-row');
                    const input = row?.querySelector('.rule-value');
                    if (input) {
                        input.value = btn.dataset.val;
                        input.focus();
                        syncToHidden();
                    }
                });

                // Авто-синхронизация при вводе в поля
                list?.addEventListener('input', syncToHidden);

                // Синхронизация raw textarea → hidden input
                rawTextarea?.addEventListener('input', () => {
                    hiddenInput.value = rawTextarea.value;
                });

                // Переключение режимов
                toggleBuilder?.addEventListener('click', () => setMode('builder'));
                toggleRaw?.addEventListener('click', () => setMode('raw'));

                // Инициализация
                renderRules();
                setMode('builder');
            });

            // Логика для поля default_value
            (function() {
                const defaultInput = document.getElementById('default_value_input');
                const defaultHidden = document.getElementById('default_value_hidden');

                if (!defaultInput || !defaultHidden) return;

                /**
                 * Парсит значение из текстового поля в правильный тип
                 */
                function parseDefaultValue(rawValue) {
                    const val = (rawValue || '').trim();
                    const lower = val.toLowerCase();

                    if (lower === 'true') return true;
                    if (lower === 'false') return false;
                    if (val === '' || lower === 'null') return null;
                    if (/^-?\d+$/.test(val)) return parseInt(val, 10);
                    if (/^-?\d+\.\d+$/.test(val)) return parseFloat(val);

                    // Строка: убираем кавычки, если есть
                    return val.replace(/^["']|["']$/g, '');
                }

                /**
                 * Обновляет скрытое поле и визуальные стили
                 */
                function syncDefaultValue() {
                    const raw = defaultInput.value.trim();
                    const parsed = parseDefaultValue(raw);

                    // Сохраняем в скрытое поле как JSON (чтобы корректно десериализовать на бэкенде)
                    defaultHidden.value = JSON.stringify(parsed);

                    // Визуальная обратная связь
                    defaultInput.classList.remove('text-primary', 'text-success', 'fw-bold', 'is-valid');
                    defaultInput.style.fontFamily = '';
                    defaultInput.style.borderColor = '';

                    if (parsed === true || parsed === false) {
                        defaultInput.classList.add('text-primary', 'fw-bold');
                        defaultInput.style.fontFamily = 'monospace';
                        defaultInput.style.borderColor = '#0d6efd';
                        defaultInput.title = `Тип: boolean (${parsed})`;
                    } else if (typeof parsed === 'number') {
                        defaultInput.classList.add('text-success');
                        defaultInput.style.fontFamily = 'monospace';
                        defaultInput.style.borderColor = '#198754';
                        defaultInput.title = `Тип: number (${parsed})`;
                    } else {
                        defaultInput.classList.add('is-valid');
                        defaultInput.title = `Тип: string ("${parsed}")`;
                    }
                }

                // Обработчик кнопок быстрого выбора
                document.addEventListener('click', function(e) {
                    const btn = e.target.closest('.quick-default');
                    if (!btn) return;
                    e.preventDefault();
                    defaultInput.value = btn.dataset.val;
                    syncDefaultValue();
                    defaultInput.focus();
                });

                // Авто-синхронизация при вводе
                defaultInput.addEventListener('input', syncDefaultValue);

                // Инициализация при загрузке
                syncDefaultValue();
            })();
        </script>
    @endpush
@endsection
