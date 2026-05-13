<div class="card">
    <div class="card-header bg-primary text-white">
        <i class="fa fa-flag me-2"></i> Feature Flags: Включаем фичи, а не баги
    </div>
    <div class="card-body">

        <!-- Коротко о главном -->
        <h3 class="mb-3">🤔 Что это вообще такое?</h3>
        <p><strong>Простыми словами:</strong> это «умный выключатель», который отвечает на вопрос:</p>
        <div class="alert alert-info">
            <i class="fa fa-lightbulb me-2"></i>
            <em>«Показывать ли этот контент этому пользователю, в этом документе, при этих условиях?»</em>
        </div>

        <p>❌ <strong>Нет</strong>, это не замена <code>сниппету IF</code>. Это как если бы <code>сниппет IF</code> выпил кофе, поумнел и научился принимать решения.</p>
        <p>✅ <strong>Да</strong>, это гибкие правила: «покажи баннер только в декабре», «дай новый шаблон 10% пользователей», «скрой кнопку для гостей».</p>

        <p class="mb-0"><small class="text-muted">
                <i class="fa fa-heart text-danger"></i> В основе — ядро <a href="https://github.com/Ambrion/feature-flags-core" target="_blank">Feature Flags Core</a>.
                Мы просто сделали для него удобную админку в стиле Evolution CMS.
            </small></p>

        <hr class="my-4">

        <!-- Быстрый старт -->
        <h3 class="mb-3">🚀 Хочу попробовать за 5 минут!</h3>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="card h-100 border-success">
                    <div class="card-header bg-success text-white">
                        <i class="fa fa-check-circle"></i> Шаг 1: Создай сниппет
                    </div>
                    <div class="card-body">
                        <p class="small mb-2">Создай сниппет <strong>FeatureFlagsDemo</strong> и вставь:</p>
                        <pre class="bg-light p-2 rounded small mb-0"><code class="language-php">return require MODX_BASE_PATH . 'core/custom/packages/featureFlags/snippets/snippet.FeatureFlagsDemo.php';</code></pre>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100 border-info">
                    <div class="card-header bg-info text-white">
                        <i class="fa fa-file-code"></i> Шаг 2: Создай документ
                    </div>
                    <div class="card-body">
                        <p class="small mb-2">Создай документ и вставь содержимое из:</p>
                        <pre class="bg-light p-2 rounded small mb-0"><code>core/custom/packages/featureFlags/snippets/demo.page.snippet.FeatureFlagsDemo.html</code></pre>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100 border-warning">
                    <div class="card-header bg-warning text-dark">
                        <i class="fa fa-cog"></i> Шаг 3: Настрой драйвер
                    </div>
                    <div class="card-body">
                        <p class="small mb-2">В <code>core/custom/.env</code> добавь:</p>
                        <pre class="bg-light p-2 rounded small mb-0"><code>FEATURE_FLAGS_DRIVER=config</code></pre>
                        <small class="text-muted">Доступно: <code>config</code> (файл) или <code>eloquent</code> (БД)</small>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100 border-primary">
                    <div class="card-header bg-primary text-white">
                        <i class="fa fa-rocket"></i> Шаг 4: Проверяй!
                    </div>
                    <div class="card-body">
                        <p class="small mb-2">Открой документ и нажимай кнопки. Смотри, что меняется.</p>
                        <p class="mb-0"><small class="text-success"><i class="fa fa-check"></i> Если всё работает — поздравляем, ты в теме!</small></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-warning mt-3 mb-0">
            <i class="fa fa-exclamation-triangle me-2"></i>
            <strong>Важно:</strong> Проверь, что файл <code>core/custom/packages/featureFlags/config/feature_flags_rules.php</code> на месте и в нём есть правила. Без них демо будет скучным, как понедельник утром.
        </div>

        <hr class="my-4">

        <!-- Сценарии использования -->
        <h3 class="mb-3">🎯 Когда это пригодится? (7 сценариев)</h3>

        <!-- Сценарий 1 -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <strong>Сценарий 1: A/B-тестирование контента</strong>
            </div>
            <div class="card-body">
                <p><strong>Проблема:</strong> Не знаешь, какой заголовок лучше конвертирует: «Купи!» или «Не упусти!»?</p>
                <p><strong>Решение:</strong> Создай флаг с правилом «50% пользователей — вариант A, 50% — вариант B».</p>

                <div class="bg-light p-3 rounded mb-3">
                    <p class="small mb-2"><strong>📁 Конфиг (feature_flags_rules.php):</strong></p>
                    <pre class="mb-0 small"><code class="language-php">'article_header_test' => [
    'default' => false,
    'rules' => [
        // 50% пользователей увидят вариант "true"
        ['condition' => 'user_hash PERCENTAGE 50', 'value' => true],
    ]
]</code></pre>
                </div>

                <div class="bg-light p-3 rounded mb-3">
                    <p class="small mb-2"><strong>💻 В сниппете:</strong></p>
                    <pre class="mb-0 small"><code class="language-php">$variant = $flags->isEnabled('article_header_test', context: [
    'user_hash' => md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'])
]);

$header = ($variant === true) ? $doc['tv_promo_title'] : $doc['pagetitle'];</code></pre>
                </div>

                <div class="bg-light p-3 rounded">
                    <p class="small mb-2"><strong>⚡ В демо-сниппете (чанк/шаблон):</strong></p>
                    <pre class="mb-0 small"><code>[!FeatureFlags? &flag=`article_header_test` &yes=`Купи!` &no=`Не упусти!`!]</code></pre>
                </div>

                <p class="mb-0 mt-3"><strong>✅ Польза:</strong> Решения на основе данных, а не «мне кажется». Тестируй без дубликатов документов.</p>
            </div>
        </div>

        <!-- Сценарий 2 -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <strong>Сценарий 2: Сезонный контент (снежинки, акции, баннеры)</strong>
            </div>
            <div class="card-body">
                <p><strong>Проблема:</strong> Каждый декабрь копи-пастить код для новогоднего баннера? Надоело.</p>
                <p><strong>Решение:</strong> Флаг с правилом по дате. Включается сам, выключается сам.</p>

                <div class="bg-light p-3 rounded mb-3">
                    <pre class="mb-0 small"><code class="language-php">'show_new_year_banner' => [
    'default' => false,
    'rules' => [
        // Активно с 1 по 31 декабря
        ['condition' => 'current_date BETWEEN 12-01 AND 12-31', 'value' => true],
    ],
]</code></pre>
                </div>

                <div class="bg-light p-3 rounded">
                    <pre class="mb-0 small"><code>[!FeatureFlags? &flag=`show_new_year_banner` &yes=`<div class="xmas-banner">🎄 С Новым Годом!</div>` &no=``!]</code></pre>
                </div>

                <p class="mb-0 mt-3"><strong>✅ Польза:</strong> Маркетинг включает акции без разработчиков. Ага, и снежинки тоже можно вовремя запустить!11 (%</p>
            </div>
        </div>

        <!-- Сценарий 3: Постепенный релиз нового шаблона -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <strong>Сценарий 3: Постепенный релиз нового шаблона</strong>
            </div>
            <div class="card-body">
                <p><strong>Проблема:</strong> Вы разработали новый шаблон карточки товара, но боитесь выкатывать его сразу на весь каталог (1000+ товаров).</p>
                <p><strong>Решение с флагами:</strong> Включите новый шаблон для 10% пользователей — детерминированно по хешу. Один и тот же пользователь всегда будет видеть один вариант.</p>

                <div class="bg-light p-3 rounded mb-3">
                    <p class="small mb-2"><strong>📁 Конфиг (feature_flags_rules.php):</strong></p>
                    <pre class="mb-0 small"><code class="language-php">'new_product_template' => [
    'default' => false,  // По умолчанию — старый шаблон
    'rules' => [
        // 10% пользователей увидят новый шаблон (по хешу)
        ['condition' => 'user_hash PERCENTAGE 10', 'value' => true],

        // Тестовые документы — всегда новый шаблон
        ['condition' => 'document_id IN (101,102,105)', 'value' => true],

        // Админам — всегда новый, чтобы тестировать
        ['condition' => 'user_role=admin', 'value' => true],
    ]
]</code></pre>
                </div>

                <div class="bg-light p-3 rounded mb-3">
                    <p class="small mb-2"><strong>💻 В сниппете вывода товара:</strong></p>
                    <pre class="mb-0 small"><code class="language-php">// Формируем стабильный хеш пользователя
$userHash = md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);

if ($flags->isEnabled('new_product_template', context: [
    'document_id' => $doc['id'],
    'user_hash' => $userHash,
    'user_role' => $modx->getLoginUserID('mgr') ? 'admin' : 'guest'
])) {
    // Новый, красивый шаблон 🎨
    return $modx->parser->parseTemplate('@FILE: assets/templates/new_product.tpl', $doc);
} else {
    // Старый, проверенный шаблон 🛠️
    return $modx->parser->parseTemplate('@FILE: assets/templates/old_product.tpl', $doc);
}</code></pre>
                </div>

                <div class="alert alert-info mb-3">
                    <i class="fa fa-lightbulb me-2"></i>
                    <strong>Как работает <code>PERCENTAGE</code>?</strong><br>
                    <small>
                        Условие <code>user_hash PERCENTAGE 10</code> вычисляет остаток от деления хеша на 100.
                        Если результат &lt; 10 — условие истинно.
                        Один и тот же хеш всегда даст один и тот же результат → пользователь не будет «прыгать» между шаблонами.
                    </small>
                </div>

                <p class="mb-0"><strong>✅ Польза:</strong></p>
                <ul class="mb-0 small">
                    <li>🛡️ Нет риска «сломать весь каталог» — новый шаблон видят только 10%</li>
                    <li>🔄 Быстрый откат: просто поменяйте `10` на `0` в конфиге, не деплоя код</li>
                    <li>📊 Можно собрать метрики: «Новый шаблон дал +12% к конверсии»</li>
                    <li>👨‍💻 Админам всегда новый — удобно тестировать без «костылей» в коде</li>
                </ul>
            </div>
        </div>

        <!-- Сценарий 4-7 (компактно) -->
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Сценарий 4: Безопасный рефакторинг</h5>
                        <p class="small">Переписываешь старый сниппет? Оберни новый код в флаг. Если что-то пошло не так — отключи флаг, старый код продолжит работать.</p>
                        <p class="mb-0"><small class="text-success"><i class="fa fa-check"></i> Мгновенный откат без деплоя</small></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Сценарий 5: Ролевой доступ</h5>
                        <p class="small">Менеджеры видят новую панель редактирования, редакторы — старую, пока не обучатся. Включай фичи постепенно.</p>
                        <p class="mb-0"><small class="text-success"><i class="fa fa-check"></i> Обучение без стресса</small></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Сценарий 6: Условные блоки</h5>
                        <p class="small">Показывай калькулятор доставки только для категорий с доставкой, или контакт менеджера — только гостям.</p>
                        <p class="mb-0"><small class="text-success"><i class="fa fa-check"></i> Логика в конфиге, а не в шаблонах</small></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Сценарий 7: Тесты производительности</h5>
                        <p class="small">Новый поиск быстрее, но «ест» больше памяти? Запусти для 5% трафика, собери метрики, прими решение.</p>
                        <p class="mb-0"><small class="text-success"><i class="fa fa-check"></i> Тесты на реальной нагрузке</small></p>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <!-- Где хранить правила? -->
        <h3 class="mb-3">🗃️ Где хранить конфигурацию флагов?</h3>

        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead class="table-light">
                <tr>
                    <th>Способ</th>
                    <th>Когда использовать</th>
                    <th>Плюсы</th>
                    <th>Минусы</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><code>config/feature_flags_rules.php</code></td>
                    <td>Статичные правила, дев-окружение</td>
                    <td>Быстро, версионируется в Git</td>
                    <td>Требует деплоя для изменений</td>
                </tr>
                <tr>
                    <td>Системные настройки ($modx->config)</td>
                    <td>Простые флаги, редко меняются</td>
                    <td>Встроенный интерфейс, кэшируется</td>
                    <td>Неудобно для сложных правил</td>
                </tr>
                <tr class="table-success">
                    <td><strong>База данных (этот модуль)</strong></td>
                    <td><strong>Динамические правила, частые изменения</strong></td>
                    <td><strong>Гибкость, админка, без деплоя</strong></td>
                    <td>Требует миграций при установке</td>
                </tr>
                <tr>
                    <td>Внешний сервис (Unleash, LaunchDarkly)</td>
                    <td>Крупные проекты, команда >5 человек</td>
                    <td>Продвинутая аналитика, API</td>
                    <td>Зависимость от внешнего сервиса</td>
                </tr>
                </tbody>
            </table>
        </div>

        <p class="mb-0"><small class="text-muted">
                <i class="fa fa-lightbulb text-warning"></i>
                <strong>Совет:</strong> Начни с конфига для демонстрации. Потом переключи драйвер на <code>eloquent</code> — и правила можно менять прямо в админке, без деплоя. Магия! ✨
            </small></p>

        <hr class="my-4">

        <!-- Чек-лист "Всё ли работает?" -->
        <h3 class="mb-3">🔧 Не работает? Проверь это:</h3>

        <div class="alert alert-light border">
            <ul class="mb-0 small">
                <li><input type="checkbox" class="me-2"> Файл <code>feature_flags_rules.php</code> на месте и не пустой?</li>
                <li><input type="checkbox" class="me-2"> В <code>.env</code> указано <code>FEATURE_FLAGS_DRIVER=config</code> (или <code>eloquent</code>)?</li>
                <li><input type="checkbox" class="me-2"> Сниппет <code>FeatureFlagsDemo</code> создан и содержит правильный <code>require</code>?</li>
                <li><input type="checkbox" class="me-2"> В документе вставлен контент из <code>demo.page.snippet.FeatureFlagsDemo.html</code>?</li>
                <li><input type="checkbox" class="me-2"> Кэш очищен? (<code>php artisan cache:clear</code> или в админке)</li>
            </ul>
        </div>

        <p class="mb-0 mt-3">
            <small class="text-muted">
                <i class="fa fa-question-circle"></i>
                Всё равно не работает? Пиши в <a href="https://github.com/Ambrion/evocms-feature-flags/issues" target="_blank">issues</a> — разберём вместе!
            </small>
        </p>

    </div>
</div>
<!-- Блок связи с автором -->
<div class="card border-primary mt-4">
    <div class="card-header bg-primary text-white">
        <i class="fa fa-user-circle me-2"></i> Связь с автором модуля
    </div>
    <div class="card-body">
        <p class="mb-3">
            <strong>Ambrion</strong> — разработчик модуля.<br>
            Есть вопросы, идеи или нашли баг? Пишите — отвечу! 🤝
        </p>

        <div class="row g-3">
            <!-- Сайт -->
            <div class="col-md-4">
                <a href="https://ambrion.dev/?site=FeatureFlags" target="_blank"
                   class="d-flex align-items-center p-3 border rounded hover-shadow text-decoration-none h-100"
                   style="transition: all 0.2s;">
                    <i class="fa fa-globe fa-2x text-primary me-3"></i>
                    <div>
                        <div class="fw-bold">Сайт</div>
                        <small class="text-muted">ambrion.dev</small>
                    </div>
                </a>
            </div>

            <!-- Telegram -->
            <div class="col-md-4">
                <a href="https://t.me/ambrion_dev" target="_blank"
                   class="d-flex align-items-center p-3 border rounded hover-shadow text-decoration-none h-100"
                   style="transition: all 0.2s;">
                    <i class="fa fa-telegram fa-2x text-info me-3"></i>
                    <div>
                        <div class="fw-bold">Telegram</div>
                        <small class="text-muted">Канал @ambrion_dev</small>
                    </div>
                </a>
            </div>

            <!-- Email -->
            <div class="col-md-4">
                <a href="mailto:ping@ambrion.dev"
                   class="d-flex align-items-center p-3 border rounded hover-shadow text-decoration-none h-100"
                   style="transition: all 0.2s;">
                    <i class="fa fa-envelope fa-2x text-success me-3"></i>
                    <div>
                        <div class="fw-bold">Email</div>
                        <small class="text-muted">ping@ambrion.dev</small>
                    </div>
                </a>
            </div>
        </div>

        <div class="alert alert-light border mt-3 mb-0 small">
            <i class="fa fa-lightbulb text-warning me-1"></i>
            <strong>Совет:</strong> Перед вопросом проверьте <a href="https://github.com/Ambrion/evocms-feature-flags/issues" target="_blank">GitHub Issues</a> — возможно, ответ уже есть!
        </div>
    </div>
</div>
