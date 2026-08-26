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
            'GET <id:\d+>' => 'view',
            'GET list' => 'index',
        ],
        'extraPatterns' => [
            'POST create' => 'create',
            'GET <id:\d+>' => 'view',
            'GET list' => 'index',
        ],
    ],
];