<?php

declare(strict_types=1);

namespace app\services;

use app\entities\CarEntity;
use app\exceptions\ValidationException;
use app\repositories\CarRepositoryInterface;
use app\validation\CarCreateValidator;
use yii\data\DataProviderInterface;

/**
 * Сервис работы с объявлениями автомобилей.
 */
final class CarService
{
    public function __construct(
        private readonly CarRepositoryInterface $carRepository,
        private readonly CarCreateValidator $createValidator,
    ) {
    }

    /**
     * Создание объявления автомобиля.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): CarEntity
    {
        $errors = $this->createValidator->validate($data);
        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $option = null;
        if (isset($data['options']) && is_array($data['options'])) {
            $option = new \app\entities\CarOptionEntity(
                (string) $data['options']['brand'],
                (string) $data['options']['model'],
                (int) $data['options']['year'],
                (string) $data['options']['body'],
                (int) $data['options']['mileage'],
            );
        }

        return $this->carRepository->saveCar(
            new CarEntity(
                null,
                (string) $data['title'],
                (string) $data['description'],
                (float) $data['price'],
                (string) $data['photo_url'],
                (string) $data['contacts'],
            ),
            $option,
        );
    }

    public function findById(int $id): ?CarEntity
    {
        return $this->carRepository->findById($id);
    }

    public function findAll(int $page, int $pageSize): DataProviderInterface
    {
        return $this->carRepository->findAll($page, $pageSize);
    }
}
