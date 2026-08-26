<?php

declare(strict_types=1);

namespace app\mappers;

use app\entities\CarEntity;
use app\entities\CarOptionEntity;
use app\models\Car;
use app\models\CarOption;

/**
 * Преобразование между доменной сущностью и ActiveRecord (DataMapper).
 */
final class CarMapper
{
    public static function fromActiveRecord(Car $car): CarEntity
    {
        $option = null;
        if ($car->option !== null) {
            $option = self::optionFromActiveRecord($car->option);
        }

        return new CarEntity(
            (int) $car->id,
            (string) $car->title,
            (string) $car->description,
            (float) $car->price,
            (string) $car->photo_url,
            (string) $car->contacts,
            $car->created_at !== null ? (string) $car->created_at : null,
            $option,
        );
    }

    public static function optionFromActiveRecord(CarOption $option): CarOptionEntity
    {
        return new CarOptionEntity(
            (string) $option->brand,
            (string) $option->model,
            (int) $option->year,
            (string) $option->body,
            (int) $option->mileage,
        );
    }
}
