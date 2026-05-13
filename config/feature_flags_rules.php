<?php

declare(strict_types=1);

return [
    'show_new_year_banner' => [
        'default' => false,
        'rules' => [
            ['condition' => 'current_date BETWEEN 12-01 AND 12-31', 'value' => true],
        ],
    ],
    'new_product_template' => [
        'default' => false,
        'rules' => [
            ['condition' => 'category=electronics', 'value' => true],
        ]
    ],
    'discount_for_users' => [
        'default' => false,
        'rules' => [
            ['condition' => 'user_role=user', 'value' => true],
            ['condition' => 'user_role=manager', 'value' => true],
        ]
    ],
    'cta_variant_test' => [
        'default' => false,
        'rules' => [
            ['condition' => 'user_hash PERCENTAGE 50', 'value' => true]
        ]
    ],
    'new_review_section' => [
        'default' => false,
        'rules' => [
            ['condition' => 'user_hash PERCENTAGE 20', 'value' => true]
        ]
    ]
];
