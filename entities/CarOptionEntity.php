<?php

declare(strict_types=1);

namespace app\entities;

/**
 * Доменная сущность тех. характеристик объявления.
 */
final class CarOptionEntity
{
    public function __construct(
        private string $brand,
        private string $model,
        private int $year,
        private string $body,
        private int $mileage,
    ) {
    }

    public function getBrand(): string
    {
        return $this->brand;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getMileage(): int
    {
        return $this->mileage;
    }

    public function toArray(): array
    {
        return [
            'brand' => $this->brand,
            'model' => $this->model,
            'year' => $this->year,
            'body' => $this->body,
            'mileage' => $this->mileage,
        ];
    }
}
