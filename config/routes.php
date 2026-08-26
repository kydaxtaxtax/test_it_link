<?php

declare(strict_types=1);

use yii\rest\UrlRule;

return [
    [
        'class' => UrlRule::class,
        'controller' => 'car',
        'pluralize' => false,
        'patterns' => [
            'POST create' => 'create',
            'GET list' => 'index',
            'GET <id:\d+>' => 'view',
        ],
        'extraPatterns' => [
            'POST create' => 'create',
            'GET list' => 'index',
            'GET <id:\d+>' => 'view',
        ],
    ],
];
