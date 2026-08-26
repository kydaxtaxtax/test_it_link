<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Тех. характеристики объявления (DataMapper над таблицей {{%car_option}}).
 *
 * @property int $id
 * @property int $car_id
 * @property string $brand
 * @property string $model
 * @property int $year
 * @property string $body
 * @property int $mileage
 * @property Car $car
 */
final class CarOption extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%car_option}}';
    }

    public function rules(): array
    {
        return [
            [['brand', 'model', 'year', 'body', 'mileage'], 'required'],
            [['car_id', 'year', 'mileage'], 'integer'],
            [['brand', 'model', 'body'], 'string', 'max' => 255],
        ];
    }

    public function getCar(): ActiveQuery
    {
        return $this->hasOne(Car::class, ['id' => 'car_id'])->inverseOf('option');
    }
}
