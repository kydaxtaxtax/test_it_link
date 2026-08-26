<?php

declare(strict_types=1);

return [
    'class' => 'yii\db\Connection',
    'dsn' => getenv('DB_DSN') ?: 'pgsql:host=localhost;port=5432;dbname=car_ad_db',
    'username' => getenv('DB_USER') ?: 'postgres',
    'password' => getenv('DB_PASSWORD') ?: 'postgres',
    'charset' => 'utf8',
];