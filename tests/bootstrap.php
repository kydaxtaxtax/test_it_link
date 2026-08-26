<?php

declare(strict_types=1);

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';

$dbDsn = getenv('DB_DSN') ?: 'sqlite:/tmp/car_ad_test.db';

$config = [
    'id' => 'car-ad-test',
    'basePath' => dirname(__DIR__),
    'components' => [
        'db' => [
            'class' => yii\db\Connection::class,
            'dsn' => $dbDsn,
            'username' => getenv('DB_USER') ?: '',
            'password' => getenv('DB_PASSWORD') ?: '',
        ],
        'cache' => [
            'class' => yii\caching\DummyCache::class,
        ],
        'request' => [
            'class' => yii\web\Request::class,
            'parsers' => ['application/json' => 'yii\web\JsonParser'],
        ],
        'response' => [
            'class' => yii\web\Response::class,
            'format' => yii\web\Response::FORMAT_JSON,
            'charset' => 'UTF-8',
        ],
    ],
    'container' => [
        'definitions' => [
            app\repositories\CarRepositoryInterface::class => static fn () => new app\repositories\CarRepository(\Yii::$app->db),
            app\services\CarService::class => static fn (app\repositories\CarRepositoryInterface $repo) => new app\services\CarService(
                $repo,
                new \app\validation\CarCreateValidator()
            ),
        ],
        'singletons' => [
            app\repositories\CarRepositoryInterface::class => app\repositories\CarRepository::class,
            app\services\CarService::class => app\services\CarService::class,
        ],
    ],
];

$app = new yii\console\Application($config);

$db = \Yii::$app->db;
$migrations = [
    'CREATE TABLE IF NOT EXISTS {{%car}} (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        price DECIMAL(12,2) NOT NULL,
        photo_url VARCHAR(500) NOT NULL,
        contacts VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )',
    'CREATE TABLE IF NOT EXISTS {{%car_option}} (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        car_id INTEGER NOT NULL,
        brand VARCHAR(255) NOT NULL,
        model VARCHAR(255) NOT NULL,
        year INTEGER NOT NULL,
        body VARCHAR(100) NOT NULL,
        mileage INTEGER NOT NULL,
        FOREIGN KEY (car_id) REFERENCES {{%car}}(id) ON DELETE CASCADE
    )',
];

foreach ($migrations as $sql) {
    try {
        $db->createCommand($sql)->execute();
    } catch (\Exception $e) {
    }
}