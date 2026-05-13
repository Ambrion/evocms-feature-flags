<div class="card">
    <div class="card-header bg-primary text-white">
        <i class="fa fa-flag me-2"></i> Feature Flags: Enable features, not bugs
    </div>
    <div class="card-body">

        <!-- What is it? -->
        <h3 class="mb-3">🤔 What is this, anyway?</h3>
        <p><strong>In simple terms:</strong> it's a "smart switch" that answers the question:</p>
        <div class="alert alert-info">
            <i class="fa fa-lightbulb me-2"></i>
            <em>"Should this content be shown to this user, in this document, under these conditions?"</em>
        </div>

        <p>❌ <strong>No</strong>, this is not a replacement for the <code>IF snippet</code>. It's like if the <code>IF snippet</code> drank coffee, got smarter, and learned to make decisions.</p>
        <p>✅ <strong>Yes</strong>, these are flexible rules: "show the banner only in December", "give the new template to 10% of users", "hide the button for guests".</p>

        <p class="mb-0"><small class="text-muted">
                <i class="fa fa-heart text-danger"></i> Powered by <a href="https://github.com/Ambrion/feature-flags-core" target="_blank">Feature Flags Core</a>.
                We just built a convenient admin UI in EvolutionCMS style.
            </small></p>

        <hr class="my-4">

        <!-- Quick Start -->
        <h3 class="mb-3">🚀 Want to try it in 5 minutes?</h3>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="card h-100 border-success">
                    <div class="card-header bg-success text-white">
                        <i class="fa fa-check-circle"></i> Step 1: Create a snippet
                    </div>
                    <div class="card-body">
                        <p class="small mb-2">Create a snippet named <strong>FeatureFlagsDemo</strong> and paste:</p>
                        <pre class="bg-light p-2 rounded small mb-0"><code class="language-php">return require MODX_BASE_PATH . 'core/vendor/ambrion/evocms-feature-flags/snippets/snippet.FeatureFlagsDemo.php';</code></pre>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100 border-info">
                    <div class="card-header bg-info text-white">
                        <i class="fa fa-file-code"></i> Step 2: Create a document
                    </div>
                    <div class="card-body">
                        <p class="small mb-2">Create a document and paste content from:</p>
                        <pre class="bg-light p-2 rounded small mb-0"><code>core/vendor/ambrion/evocms-feature-flags/snippets/demo.page.snippet.FeatureFlagsDemo.html</code></pre>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100 border-warning">
                    <div class="card-header bg-warning text-dark">
                        <i class="fa fa-cog"></i> Step 3: Configure the driver
                    </div>
                    <div class="card-body">
                        <p class="small mb-2">In <code>core/custom/.env</code> add:</p>
                        <pre class="bg-light p-2 rounded small mb-0"><code>FEATURE_FLAGS_DRIVER=config</code></pre>
                        <small class="text-muted">Available: <code>config</code> (file) or <code>eloquent</code> (database)</small>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100 border-primary">
                    <div class="card-header bg-primary text-white">
                        <i class="fa fa-rocket"></i> Step 4: Test it!
                    </div>
                    <div class="card-body">
                        <p class="small mb-2">Open the document and click buttons. See what changes.</p>
                        <p class="mb-0"><small class="text-success"><i class="fa fa-check"></i> If it works — congrats, you're in!</small></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-warning mt-3 mb-0">
            <i class="fa fa-exclamation-triangle me-2"></i>
            <strong>Important:</strong> Make sure <code>core/vendor/ambrion/evocms-feature-flags/config/feature_flags_rules.php</code> exists and contains rules. Without them, the demo will be as boring as a Monday morning.
        </div>

        <hr class="my-4">

        <!-- Use Cases -->
        <h3 class="mb-3">🎯 When will you need this? (7 scenarios)</h3>

        <!-- Scenario 1 -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <strong>Scenario 1: A/B testing content</strong>
            </div>
            <div class="card-body">
                <p><strong>Problem:</strong> Not sure which headline converts better: "Buy Now!" or "Don't Miss Out!"?</p>
                <p><strong>Solution:</strong> Create a flag with a rule "50% of users see variant A, 50% see variant B".</p>

                <div class="bg-light p-3 rounded mb-3">
                    <p class="small mb-2"><strong>📁 Config (feature_flags_rules.php):</strong></p>
                    <pre class="mb-0 small"><code class="language-php">'article_header_test' => [
    'default' => false,
    'rules' => [
        // 50% of users will see "true"
        ['condition' => 'user_hash PERCENTAGE 50', 'value' => true],
    ]
]</code></pre>
                </div>

                <div class="bg-light p-3 rounded mb-3">
                    <p class="small mb-2"><strong>💻 In a snippet:</strong></p>
                    <pre class="mb-0 small"><code class="language-php">$variant = $flags->isEnabled('article_header_test', context: [
    'user_hash' => md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'])
]);

$header = ($variant === true) ? $doc['tv_promo_title'] : $doc['pagetitle'];</code></pre>
                </div>

                <div class="bg-light p-3 rounded">
                    <p class="small mb-2"><strong>⚡ In the demo snippet (chunk/template):</strong></p>
                    <pre class="mb-0 small"><code>[!FeatureFlags? &flag=`article_header_test` &yes=`Buy Now!` &no=`Don't Miss Out!`!]</code></pre>
                </div>

                <p class="mb-0 mt-3"><strong>✅ Benefit:</strong> Make decisions based on data, not guesses. Test without duplicating documents.</p>
            </div>
        </div>

        <!-- Scenario 2 -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <strong>Scenario 2: Seasonal content (snowflakes, promotions, banners)</strong>
            </div>
            <div class="card-body">
                <p><strong>Problem:</strong> Tired of copy-pasting code for the New Year banner every December?</p>
                <p><strong>Solution:</strong> A flag with a date-based rule. Turns on automatically, turns off automatically.</p>

                <div class="bg-light p-3 rounded mb-3">
                    <pre class="mb-0 small"><code class="language-php">'show_new_year_banner' => [
    'default' => false,
    'rules' => [
        // Active from Dec 1 to Dec 31
        ['condition' => 'current_date BETWEEN 12-01 AND 12-31', 'value' => true],
    ],
]</code></pre>
                </div>

                <div class="bg-light p-3 rounded">
                    <pre class="mb-0 small"><code>[!FeatureFlags? &flag=`show_new_year_banner` &yes=`<div class="xmas-banner">🎄 Happy New Year!</div>` &no=``!]</code></pre>
                </div>

                <p class="mb-0 mt-3"><strong>✅ Benefit:</strong> Marketing can toggle promotions without developers. Yep, you can even schedule snowflakes on time!11 (%</p>
            </div>
        </div>

        <!-- Scenario 3 -->
        <div class="card mb-3">
            <div class="card-header bg-light">
                <strong>Scenario 3: Gradual rollout of a new template</strong>
            </div>
            <div class="card-body">
                <p><strong>Problem:</strong> You've built a new product card template, but you're scared to roll it out to the entire catalog (1000+ items) at once.</p>
                <p><strong>Solution with flags:</strong> Enable the new template for 10% of users — deterministically by hash. The same user will always see the same variant.</p>

                <div class="bg-light p-3 rounded mb-3">
                    <p class="small mb-2"><strong>📁 Config (feature_flags_rules.php):</strong></p>
                    <pre class="mb-0 small"><code class="language-php">'new_product_template' => [
    'default' => false,  // Default: old template
    'rules' => [
        // 10% of users will see the new template (by hash)
        ['condition' => 'user_hash PERCENTAGE 10', 'value' => true],

        // Test documents always get the new template
        ['condition' => 'document_id IN (101,102,105)', 'value' => true],

        // Admins always get the new template for testing
        ['condition' => 'user_role=admin', 'value' => true],
    ]
]</code></pre>
                </div>

                <div class="bg-light p-3 rounded mb-3">
                    <p class="small mb-2"><strong>💻 In the product output snippet:</strong></p>
                    <pre class="mb-0 small"><code class="language-php">// Generate a stable user hash
$userHash = md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);

if ($flags->isEnabled('new_product_template', context: [
    'document_id' => $doc['id'],
    'user_hash' => $userHash,
    'user_role' => $modx->getLoginUserID('mgr') ? 'admin' : 'guest'
])) {
    // New, beautiful template 🎨
    return $modx->parser->parseTemplate('@FILE: assets/templates/new_product.tpl', $doc);
} else {
    // Old, reliable template 🛠️
    return $modx->parser->parseTemplate('@FILE: assets/templates/old_product.tpl', $doc);
}</code></pre>
                </div>

                <div class="alert alert-info mb-3">
                    <i class="fa fa-lightbulb me-2"></i>
                    <strong>How does <code>PERCENTAGE</code> work?</strong><br>
                    <small>
                        The condition <code>user_hash PERCENTAGE 10</code> computes the remainder of the hash divided by 100.
                        If the result is &lt; 10, the condition is true.
                        The same hash always produces the same result → users won't "jump" between templates.
                    </small>
                </div>

                <p class="mb-0"><strong>✅ Benefits:</strong></p>
                <ul class="mb-0 small">
                    <li>🛡️ No risk of "breaking the entire catalog" — only 10% see the new template</li>
                    <li>🔄 Quick rollback: just change `10` to `0` in config, no code deploy needed</li>
                    <li>📊 Collect metrics: "New template increased conversions by +12%"</li>
                    <li>👨‍💻 Admins always see the new version — convenient for testing without hacks</li>
                </ul>
            </div>
        </div>

        <!-- Scenarios 4-7 (compact) -->
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Scenario 4: Safe refactoring</h5>
                        <p class="small">Rewriting an old snippet? Wrap the new code in a flag. If something goes wrong — disable the flag, the old code keeps working.</p>
                        <p class="mb-0"><small class="text-success"><i class="fa fa-check"></i> Instant rollback without deploy</small></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Scenario 5: Role-based access</h5>
                        <p class="small">Managers see the new editing panel, editors see the old one until trained. Roll out features gradually.</p>
                        <p class="mb-0"><small class="text-success"><i class="fa fa-check"></i> Stress-free onboarding</small></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Scenario 6: Conditional blocks</h5>
                        <p class="small">Show the delivery calculator only for categories with delivery, or the manager contact only to guests.</p>
                        <p class="mb-0"><small class="text-success"><i class="fa fa-check"></i> Logic in config, not in templates</small></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Scenario 7: Performance experiments</h5>
                        <p class="small">New search is faster but uses more memory? Roll it out to 5% of traffic, collect metrics, decide.</p>
                        <p class="mb-0"><small class="text-success"><i class="fa fa-check"></i> Test on real load</small></p>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <!-- Where to store rules? -->
        <h3 class="mb-3">🗃️ Where to store flag configuration?</h3>

        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead class="table-light">
                <tr>
                    <th>Method</th>
                    <th>When to use</th>
                    <th>Pros</th>
                    <th>Cons</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><code>config/feature_flags_rules.php</code></td>
                    <td>Static rules, dev environment</td>
                    <td>Fast, versioned in Git</td>
                    <td>Requires deploy for changes</td>
                </tr>
                <tr>
                    <td>System settings ($modx->config)</td>
                    <td>Simple flags, rarely changed</td>
                    <td>Built-in UI, cached</td>
                    <td>Awkward for complex rules</td>
                </tr>
                <tr class="table-success">
                    <td><strong>Database (this module)</strong></td>
                    <td><strong>Dynamic rules, frequent changes</strong></td>
                    <td><strong>Flexible, admin UI, no deploy</strong></td>
                    <td>Requires migrations on install</td>
                </tr>
                <tr>
                    <td>External service (Unleash, LaunchDarkly)</td>
                    <td>Large projects, team >5 people</td>
                    <td>Advanced analytics, API</td>
                    <td>Dependency on external service</td>
                </tr>
                </tbody>
            </table>
        </div>

        <p class="mb-0"><small class="text-muted">
                <i class="fa fa-lightbulb text-warning"></i>
                <strong>Tip:</strong> Start with config for demo. Then switch driver to <code>eloquent</code> — and you can edit rules right in the admin panel, no deploy needed. Magic! ✨
            </small></p>

        <hr class="my-4">

        <!-- Troubleshooting checklist -->
        <h3 class="mb-3">🔧 Not working? Check this:</h3>

        <div class="alert alert-light border">
            <ul class="mb-0 small">
                <li><input type="checkbox" class="me-2"> Is <code>feature_flags_rules.php</code> in place and not empty?</li>
                <li><input type="checkbox" class="me-2"> Does <code>.env</code> have <code>FEATURE_FLAGS_DRIVER=config</code> (or <code>eloquent</code>)?</li>
                <li><input type="checkbox" class="me-2"> Is snippet <code>FeatureFlagsDemo</code> created with correct <code>require</code>?</li>
                <li><input type="checkbox" class="me-2"> Is content from <code>demo.page.snippet.FeatureFlagsDemo.html</code> pasted in the document?</li>
                <li><input type="checkbox" class="me-2"> Is cache cleared? (<code>php artisan cache:clear</code> or in admin)</li>
            </ul>
        </div>

        <p class="mb-0 mt-3">
            <small class="text-muted">
                <i class="fa fa-question-circle"></i>
                Still not working? Write to <a href="https://github.com/Ambrion/evocms-feature-flags/issues" target="_blank">GitHub Issues</a> — we'll figure it out together!
            </small>
        </p>

    </div>
</div>

<!-- Author contact block -->
<div class="card border-primary mt-4">
    <div class="card-header bg-primary text-white">
        <i class="fa fa-user-circle me-2"></i> Contact the module author
    </div>
    <div class="card-body">
        <p class="mb-3">
            <strong>Ambrion</strong> — module developer.<br>
            Questions, ideas, or found a bug? Write — I'll respond! 🤝
        </p>

        <div class="row g-3">
            <!-- Website -->
            <div class="col-md-4">
                <a href="https://ambrion.dev/?site=FeatureFlags" target="_blank"
                   class="d-flex align-items-center p-3 border rounded hover-shadow text-decoration-none h-100"
                   style="transition: all 0.2s;">
                    <i class="fa fa-globe fa-2x text-primary me-3"></i>
                    <div>
                        <div class="fw-bold">Website</div>
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
                        <small class="text-muted">Channel @ambrion_dev</small>
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
            <strong>Tip:</strong> Before asking, check <a href="https://github.com/Ambrion/evocms-feature-flags/issues" target="_blank">GitHub Issues</a> — maybe the answer is already there!
        </div>
    </div>
</div>
