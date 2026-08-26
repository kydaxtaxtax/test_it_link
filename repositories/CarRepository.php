<?php

declare(strict_types=1);

namespace app\repositories;

use app\entities\CarEntity;
use app\entities\CarOptionEntity;
use app\mappers\CarMapper;
use app\models\Car;
use app\models\CarOption;
use yii\data\ActiveDataProvider;
use yii\data\DataProviderInterface;
use yii\db\Connection;

/**
 * Репозиторий объявлений автомобилей (доступ к данным через DataMapper/AR).
 */
final class CarRepository implements CarRepositoryInterface
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function findById(int $id): ?CarEntity
    {
        $car = Car::find()
            ->with('option')
            ->where(['id' => $id])
            ->one();

        return $car === null ? null : CarMapper::fromActiveRecord($car);
    }

    public function findAll(int $page, int $pageSize): DataProviderInterface
    {
        return new ActiveDataProvider([
            'query' => Car::find()->with('option')->orderBy(['id' => SORT_DESC]),
            'pagination' => [
                'page' => max(0, $page - 1),
                'pageSize' => $pageSize,
                'pageSizeLimit' => false,
            ],
        ]);
    }

    public function count(): int
    {
        return (int) Car::find()->count();
    }

    public function saveCar(CarEntity $entity, ?CarOptionEntity $option): CarEntity
    {
        $transaction = $this->db->beginTransaction();

        try {
            $car = new Car();
            $car->title = $entity->getTitle();
            $car->description = $entity->getDescription();
            $car->price = (string) $entity->getPrice();
            $car->photo_url = $entity->getPhotoUrl();
            $car->contacts = $entity->getContacts();

            if (!$car->save()) {
                throw new \RuntimeException('Не удалось сохранить объявление: ' . implode('; ', $car->getFirstErrors()));
            }

            $car->refresh();

            if ($option !== null) {
                $carOption = new CarOption();
                $carOption->car_id = (int) $car->id;
                $carOption->brand = $option->getBrand();
                $carOption->model = $option->getModel();
                $carOption->year = $option->getYear();
                $carOption->body = $option->getBody();
                $carOption->mileage = $option->getMileage();

                if (!$carOption->save()) {
                    throw new \RuntimeException(
                        'Не удалось сохранить тех. характеристики: ' . implode('; ', $carOption->getFirstErrors())
                    );
                }
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        return $this->findById((int) $car->id)
            ?? throw new \RuntimeException('Объявление не найдено после сохранения');
    }
}
