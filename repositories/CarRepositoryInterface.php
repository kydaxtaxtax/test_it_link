<?php

declare(strict_types=1);

namespace app\repositories;

use app\entities\CarEntity;
use app\entities\CarOptionEntity;
use yii\data\DataProviderInterface;

interface CarRepositoryInterface
{
    public function findById(int $id): ?CarEntity;

    public function findAll(int $page, int $pageSize): DataProviderInterface;

    public function count(): int;

    /**
     * @param list<CarEntity> $entities
     */
    public function saveCar(CarEntity $entity, ?CarOptionEntity $option): CarEntity;
}
