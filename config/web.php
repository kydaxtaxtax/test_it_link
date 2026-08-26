<?php

declare(strict_types=1);

use yii\helpers\ArrayHelper;

$common = require __DIR__ . '/common.php';

$config = ArrayHelper::merge($common, [
    'id' => 'car-ad-web',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'container' => require __DIR__ . '/di.php',
    'components' => [
        'request' => [
            'cookieValidationKey' => 'change-this-cookie-key',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'response' => [
            'class' => 'yii\web\Response',
            'format' => yii\web\Response::FORMAT_JSON,
            'charset' => 'UTF-8',
        ],
        'errorHandler' => [
            'errorAction' => null,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'rules' => require __DIR__ . '/routes.php',
        ],
    ],
]);

return $config;
