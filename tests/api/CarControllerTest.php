<?php

declare(strict_types=1);

namespace tests\api;

use app\controllers\CarController;
use app\entities\CarEntity;
use app\repositories\CarRepositoryInterface;
use app\services\CarService;
use app\validation\CarCreateValidator;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\web\Application;

/**
 * API тесты REST-эндпоинтов CarController.
 */
final class CarControllerTest extends TestCase
{
    private CarService $service;
    private CarRepositoryInterface $repository;
    private array $savedCars = [];
    private int $carIdCounter = 1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new class implements CarRepositoryInterface {
            private array $cars = [];
            private int $idCounter = 1;

            public function findById(int $id): ?CarEntity
            {
                foreach ($this->cars as $car) {
                    if ($car->getId() === $id) {
                        return $car;
                    }
                }
                return null;
            }

            public function findAll(int $page, int $pageSize): \yii\data\DataProviderInterface
            {
                $offset = ($page - 1) * $pageSize;
                $total = count($this->cars);
                $subset = array_slice($this->cars, $offset, $pageSize);

                return new \yii\data\ArrayDataProvider([
                    'allModels' => $subset,
                    'totalCount' => $total,
                    'pagination' => [
                        'page' => $page - 1,
                        'pageSize' => $pageSize,
                    ],
                ]);
            }

            public function count(): int
            {
                return count($this->cars);
            }

            public function saveCar(CarEntity $entity, ?\app\entities\CarOptionEntity $option): CarEntity
            {
                $id = $this->idCounter++;
                $saved = new CarEntity(
                    $id,
                    $entity->getTitle(),
                    $entity->getDescription(),
                    $entity->getPrice(),
                    $entity->getPhotoUrl(),
                    $entity->getContacts(),
                    date('Y-m-d H:i:s'),
                    $option,
                );
                $this->cars[] = $saved;
                return $saved;
            }
        };

        $this->service = new CarService(
            $this->repository,
            new CarCreateValidator()
        );

        $this->initYiiApp();
    }

    private function initYiiApp(): void
    {
        $config = [
            'id' => 'car-api-test',
            'basePath' => dirname(__DIR__, 2),
            'components' => [
                'db' => [
                    'class' => \yii\db\Connection::class,
                    'dsn' => 'sqlite::memory:',
                ],
                'request' => [
                    'class' => \yii\web\Request::class,
                    'parsers' => ['application/json' => \yii\web\JsonParser::class],
                    'bodyParams' => [],
                ],
                'response' => [
                    'class' => \yii\web\Response::class,
                    'format' => \yii\web\Response::FORMAT_JSON,
                ],
                'urlManager' => [
                    'enablePrettyUrl' => true,
                    'showScriptName' => false,
                ],
                'errorHandler' => [
                    'class' => \yii\web\ErrorHandler::class,
                ],
            ],
        ];

        new Application($config);

        Yii::$container->setDefinitions([
            CarService::class => fn () => $this->service,
            CarController::class => fn () => new CarController('car', Yii::$app, $this->service),
        ]);
    }

    public function testCreateReturns201WithValidData(): void
    {
        $data = [
            'title' => 'BMW X5',
            'description' => 'Отличное состояние',
            'price' => 5500000,
            'photo_url' => 'https://example.com/bmw.jpg',
            'contacts' => '+7 999 123-45-67',
        ];

        $controller = new CarController('car', Yii::$app, $this->service);
        $controller->request->setBodyParams($data);

        $response = $controller->actionCreate();

        $this->assertArrayHasKey('id', $response);
        $this->assertSame('BMW X5', $response['title']);
        $this->assertSame(5500000.0, $response['price']);
        $this->assertNull($response['options']);
        $this->assertSame(201, Yii::$app->response->statusCode);
    }

    public function testCreateReturns201WithOptions(): void
    {
        $data = [
            'title' => 'Toyota Camry',
            'description' => 'Бизнес-седан',
            'price' => 3200000,
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

        $controller = new CarController('car', Yii::$app, $this->service);
        $controller->request->setBodyParams($data);

        $response = $controller->actionCreate();

        $this->assertSame('Toyota Camry', $response['title']);
        $this->assertNotNull($response['options']);
        $this->assertSame('Toyota', $response['options']['brand']);
        $this->assertSame(2022, $response['options']['year']);
        $this->assertSame(201, Yii::$app->response->statusCode);
    }

    public function testCreateReturns422WithMissingTitle(): void
    {
        $data = [
            'description' => 'Description without title',
            'price' => 1000000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ];

        $controller = new CarController('car', Yii::$app, $this->service);
        $controller->request->setBodyParams($data);

        $response = $controller->actionCreate();

        $this->assertSame(422, Yii::$app->response->statusCode);
        $this->assertArrayHasKey('errors', $response);
        $this->assertArrayHasKey('title', $response['errors']);
    }

    public function testCreateReturns422WithEmptyTitle(): void
    {
        $data = [
            'title' => '',
            'description' => 'Description',
            'price' => 1000000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ];

        $controller = new CarController('car', Yii::$app, $this->service);
        $controller->request->setBodyParams($data);

        $response = $controller->actionCreate();

        $this->assertSame(422, Yii::$app->response->statusCode);
        $this->assertArrayHasKey('title', $response['errors']);
    }

    public function testCreateReturns422WithNegativePrice(): void
    {
        $data = [
            'title' => 'Test Car',
            'description' => 'Description',
            'price' => -50000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ];

        $controller = new CarController('car', Yii::$app, $this->service);
        $controller->request->setBodyParams($data);

        $response = $controller->actionCreate();

        $this->assertSame(422, Yii::$app->response->statusCode);
        $this->assertArrayHasKey('price', $response['errors']);
    }

    public function testCreateReturns422WithMissingOptionsFields(): void
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

        $controller = new CarController('car', Yii::$app, $this->service);
        $controller->request->setBodyParams($data);

        $response = $controller->actionCreate();

        $this->assertSame(422, Yii::$app->response->statusCode);
        $this->assertArrayHasKey('errors', $response);
        $this->assertArrayHasKey('options.model', $response['errors']);
        $this->assertArrayHasKey('options.year', $response['errors']);
        $this->assertArrayHasKey('options.body', $response['errors']);
        $this->assertArrayHasKey('options.mileage', $response['errors']);
    }

    public function testCreateReturns422WithInvalidYearInOptions(): void
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
                'year' => 1700,
                'body' => 'кроссовер',
                'mileage' => 100000,
            ],
        ];

        $controller = new CarController('car', Yii::$app, $this->service);
        $controller->request->setBodyParams($data);

        $response = $controller->actionCreate();

        $this->assertSame(422, Yii::$app->response->statusCode);
        $this->assertArrayHasKey('options.year', $response['errors']);
    }

    public function testViewReturns200ForExistingCar(): void
    {
        $data = [
            'title' => 'View Test Car',
            'description' => 'Description',
            'price' => 500000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ];

        $controller = new CarController('car', Yii::$app, $this->service);
        $controller->request->setBodyParams($data);
        $created = $controller->actionCreate();

        $viewController = new CarController('car', Yii::$app, $this->service);
        $response = $viewController->actionView((int) $created['id']);

        $this->assertSame('View Test Car', $response['title']);
        $this->assertSame(200, Yii::$app->response->statusCode);
    }

    public function testViewReturns404ForNonExistentCar(): void
    {
        $controller = new CarController('car', Yii::$app, $this->service);

        $response = $controller->actionView(9999);

        $this->assertSame(404, Yii::$app->response->statusCode);
        $this->assertArrayHasKey('errors', $response);
    }

    public function testListReturns200WithPagination(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $controller = new CarController('car', Yii::$app, $this->service);
            $controller->request->setBodyParams([
                'title' => "Car $i",
                'description' => "Description $i",
                'price' => $i * 100000,
                'photo_url' => "https://example.com/car$i.jpg",
                'contacts' => "+7 999 000-$i",
            ]);
            $controller->actionCreate();
        }

        $listController = new CarController('car', Yii::$app, $this->service);
        $response = $listController->actionIndex(1);

        $this->assertArrayHasKey('items', $response);
        $this->assertArrayHasKey('pagination', $response);
        $this->assertCount(3, $response['items']);
        $this->assertSame(1, $response['pagination']['page']);
        $this->assertSame(3, $response['pagination']['totalCount']);
        $this->assertSame(200, Yii::$app->response->statusCode);
    }

    public function testListPagination(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $controller = new CarController('car', Yii::$app, $this->service);
            $controller->request->setBodyParams([
                'title' => "Page Test Car $i",
                'description' => "Description $i",
                'price' => $i * 100000,
                'photo_url' => "https://example.com/car$i.jpg",
                'contacts' => "+7 999 000-$i",
            ]);
            $controller->actionCreate();
        }

        $listController = new CarController('car', Yii::$app, $this->service);
        $response = $listController->actionIndex(2);

        $this->assertSame(2, $response['pagination']['page']);
    }

    public function testCreateReturns422WithMissingDescription(): void
    {
        $data = [
            'title' => 'Car Without Description',
            'price' => 500000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ];

        $controller = new CarController('car', Yii::$app, $this->service);
        $controller->request->setBodyParams($data);

        $response = $controller->actionCreate();

        $this->assertSame(422, Yii::$app->response->statusCode);
        $this->assertArrayHasKey('description', $response['errors']);
    }

    public function testCreateReturns422WithMissingContacts(): void
    {
        $data = [
            'title' => 'Car Without Contacts',
            'description' => 'Description',
            'price' => 500000,
            'photo_url' => 'https://example.com/photo.jpg',
        ];

        $controller = new CarController('car', Yii::$app, $this->service);
        $controller->request->setBodyParams($data);

        $response = $controller->actionCreate();

        $this->assertSame(422, Yii::$app->response->statusCode);
        $this->assertArrayHasKey('contacts', $response['errors']);
    }

    public function testCreateReturns201WithNullOptions(): void
    {
        $data = [
            'title' => 'Car With Null Options',
            'description' => 'Description',
            'price' => 500000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
            'options' => null,
        ];

        $controller = new CarController('car', Yii::$app, $this->service);
        $controller->request->setBodyParams($data);

        $response = $controller->actionCreate();

        $this->assertSame(201, Yii::$app->response->statusCode);
        $this->assertNull($response['options']);
    }

    public function testCreateReturns201WithoutOptionsField(): void
    {
        $data = [
            'title' => 'Car Without Options Field',
            'description' => 'Description',
            'price' => 500000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ];

        $controller = new CarController('car', Yii::$app, $this->service);
        $controller->request->setBodyParams($data);

        $response = $controller->actionCreate();

        $this->assertSame(201, Yii::$app->response->statusCode);
        $this->assertNull($response['options']);
    }
}