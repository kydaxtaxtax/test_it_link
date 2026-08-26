<?php

declare(strict_types=1);

namespace tests\unit\services;

use app\entities\CarEntity;
use app\exceptions\ValidationException;
use app\repositories\CarRepositoryInterface;
use app\services\CarService;
use app\validation\CarCreateValidator;
use PHPUnit\Framework\TestCase;
use tests\bootstrap;

/**
 * Unit-тесты CarService.
 */
final class CarServiceTest extends TestCase
{
    private CarService $service;
    private CarRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new class implements CarRepositoryInterface {
            public ?CarEntity $savedCar = null;

            public function findById(int $id): ?CarEntity
            {
                return $this->savedCar;
            }

            public function findAll(int $page, int $pageSize)
            {
                return new \yii\data\ArrayDataProvider([
                    'models' => [],
                    'totalCount' => 0,
                ]);
            }

            public function count(): int
            {
                return 0;
            }

            public function saveCar(CarEntity $entity, ?\app\entities\CarOptionEntity $option): CarEntity
            {
                $this->savedCar = new CarEntity(
                    1,
                    $entity->getTitle(),
                    $entity->getDescription(),
                    $entity->getPrice(),
                    $entity->getPhotoUrl(),
                    $entity->getContacts(),
                    date('Y-m-d H:i:s'),
                    $option,
                );

                return $this->savedCar;
            }
        };

        $this->service = new CarService(
            $this->repository,
            new CarCreateValidator()
        );
    }

    public function testCreateWithValidData(): void
    {
        $data = [
            'title' => 'BMW X5 2020',
            'description' => 'Отличное авто в отличном состоянии',
            'price' => 5500000.00,
            'photo_url' => 'https://example.com/bmw.jpg',
            'contacts' => '+7 999 123-45-67',
        ];

        $car = $this->service->create($data);

        $this->assertInstanceOf(CarEntity::class, $car);
        $this->assertSame('BMW X5 2020', $car->getTitle());
        $this->assertSame(5500000.00, $car->getPrice());
        $this->assertNull($car->getOption());
    }

    public function testCreateWithValidDataAndOptions(): void
    {
        $data = [
            'title' => 'Toyota Camry',
            'description' => 'Седан бизнес-класса',
            'price' => 3200000.00,
            'photo_url' => 'https://example.com/camry.jpg',
            'contacts' => '+7 999 765-43-21',
            'options' => [
                'brand' => 'Toyota',
                'model' => 'Camry',
                'year' => 2022,
                'body' => 'седан',
                'mileage' => 15000,
            ],
        ];

        $car = $this->service->create($data);

        $this->assertInstanceOf(CarEntity::class, $car);
        $this->assertNotNull($car->getOption());
        $this->assertSame('Toyota', $car->getOption()->getBrand());
        $this->assertSame('Camry', $car->getOption()->getModel());
        $this->assertSame(2022, $car->getOption()->getYear());
        $this->assertSame('седан', $car->getOption()->getBody());
        $this->assertSame(15000, $car->getOption()->getMileage());
    }

    public function testCreateWithMissingTitle(): void
    {
        $data = [
            'description' => 'Описание',
            'price' => 1000000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ];

        $this->expectException(ValidationException::class);
        $this->service->create($data);
    }

    public function testCreateWithEmptyTitle(): void
    {
        $data = [
            'title' => '',
            'description' => 'Описание',
            'price' => 1000000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ];

        try {
            $this->service->create($data);
            $this->fail('Expected ValidationException not thrown');
        } catch (ValidationException $e) {
            $errors = $e->getFieldErrors();
            $this->assertArrayHasKey('title', $errors);
        }
    }

    public function testCreateWithNegativePrice(): void
    {
        $data = [
            'title' => 'Test Car',
            'description' => 'Description',
            'price' => -50000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ];

        try {
            $this->service->create($data);
            $this->fail('Expected ValidationException not thrown');
        } catch (ValidationException $e) {
            $errors = $e->getFieldErrors();
            $this->assertArrayHasKey('price', $errors);
        }
    }

    public function testCreateWithMissingOptionsFields(): void
    {
        $data = [
            'title' => 'Test Car',
            'description' => 'Description',
            'price' => 1000000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
            'options' => [
                'brand' => 'BMW',
            ],
        ];

        try {
            $this->service->create($data);
            $this->fail('Expected ValidationException not thrown');
        } catch (ValidationException $e) {
            $errors = $e->getFieldErrors();
            $this->assertArrayHasKey('options.model', $errors);
            $this->assertArrayHasKey('options.year', $errors);
            $this->assertArrayHasKey('options.body', $errors);
            $this->assertArrayHasKey('options.mileage', $errors);
        }
    }

    public function testCreateWithOptionsYearTooOld(): void
    {
        $data = [
            'title' => 'Test Car',
            'description' => 'Description',
            'price' => 1000000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
            'options' => [
                'brand' => 'BMW',
                'model' => 'X5',
                'year' => 1800,
                'body' => 'кроссовер',
                'mileage' => 100000,
            ],
        ];

        try {
            $this->service->create($data);
            $this->fail('Expected ValidationException not thrown');
        } catch (ValidationException $e) {
            $errors = $e->getFieldErrors();
            $this->assertArrayHasKey('options.year', $errors);
        }
    }

    public function testFindByIdReturnsNullWhenNotFound(): void
    {
        $result = $this->service->findById(9999);
        $this->assertNull($result);
    }

    public function testFindByIdReturnsCar(): void
    {
        $data = [
            'title' => 'Find Test Car',
            'description' => 'Description',
            'price' => 500000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ];

        $saved = $this->service->create($data);
        $found = $this->service->findById(1);

        $this->assertNotNull($found);
        $this->assertSame($saved->getTitle(), $found->getTitle());
    }
}