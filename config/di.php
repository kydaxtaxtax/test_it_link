<?php

declare(strict_types=1);

use app\repositories\CarRepository;
use app\repositories\CarRepositoryInterface;
use app\services\CarService;

/**
 * Настройка Dependency Injection контейнера Yii2.
 * Connection инжектится через фабрику, которая safely читает \Yii::$app->db
 * после инициализации приложения (избегаем рекурсии).
 */
return [
    'definitions' => [
        CarRepositoryInterface::class => static fn () => new CarRepository(\Yii::$app->db),
        CarService::class => static fn (CarRepositoryInterface $repo) => new CarService(
            $repo,
            new \app\validation\CarCreateValidator()
        ),
    ],
    'singletons' => [
        CarRepositoryInterface::class => CarRepository::class,
        CarService::class => CarService::class,
    ],
];