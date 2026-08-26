<?php

declare(strict_types=1);

use yii\helpers\ArrayHelper;

$common = require __DIR__ . '/common.php';

$config = ArrayHelper::merge($common, [
    'id' => 'car-ad-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'container' => require __DIR__ . '/di.php',
    'controllerMap' => [
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'migrationPath' => '@app/migrations',
            'migrationTable' => '{{%migration}}',
        ],
    ],
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
    ],
]);

return $config;
