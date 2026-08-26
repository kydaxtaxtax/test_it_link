<?php

declare(strict_types=1);

return [
    'name' => 'Car Advertisement Service',
    'aliases' => [
        '@root' => dirname(__DIR__),
        '@app' => dirname(__DIR__),
        '@runtime' => dirname(__DIR__) . '/runtime',
        '@webroot' => dirname(__DIR__) . '/web',
        '@web' => '/',
        '@tests' => dirname(__DIR__) . '/tests',
    ],
    'components' => [
        'db' => require __DIR__ . '/db.php',
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
            ],
        ],
    ],
    'params' => [
        'adminEmail' => 'admin@example.com',
        'paginationPageSize' => 20,
    ],
];
