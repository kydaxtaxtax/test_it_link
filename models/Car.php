<?php

declare(strict_types=1);

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Объявление автомобиля (DataMapper над таблицей {{%car}}).
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $price
 * @property string $photo_url
 * @property string $contacts
 * @property string|null $created_at
 * @property CarOption|null $option
 */
final class Car extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%car}}';
    }

    public function rules(): array
    {
        return [
            [['title', 'description', 'price', 'photo_url', 'contacts'], 'required'],
            [['title', 'photo_url', 'contacts'], 'string', 'max' => 255],
            ['description', 'string'],
            ['price', 'number', 'min' => 0],
        ];
    }

    public function getOption(): ActiveQuery
    {
        return $this->hasOne(CarOption::class, ['car_id' => 'id'])->inverseOf('car');
    }
}
