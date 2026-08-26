<?php

declare(strict_types=1);

use app\repositories\CarRepository;
use app\repositories\CarRepositoryInterface;
use app\services\CarService;

/**
 * Настройка Dependency Injection контейнера Yii2.
 */
return [
    'definitions' => [
        CarRepositoryInterface::class => CarRepository::class,
        CarService::class => static fn () => new CarService(
            \Yii::$container->get(CarRepositoryInterface::class),
            new \app\validation\CarCreateValidator()
        ),
    ],
    'singletons' => [
        CarRepositoryInterface::class => CarRepository::class,
        CarService::class => CarService::class,
    ],
];