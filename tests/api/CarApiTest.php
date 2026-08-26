<?php

declare(strict_types=1);

namespace tests\api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use PHPUnit\Framework\TestCase;

/**
 * Интеграционные API тесты — реальные HTTP запросы к REST API.
 * Требует запущенного docker-compose (http://localhost:80).
 */
final class CarApiTest extends TestCase
{
    private static Client $client;
    private static string $baseUri = 'http://localhost:80';

    public static function setUpBeforeClass(): void
    {
        self::$client = new Client([
            'base_uri' => self::$baseUri,
            'timeout' => 10,
            'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
        ]);

        try {
            $health = self::$client->get('/health');
            if ($health->getStatusCode() !== 200) {
                throw new \RuntimeException('Health check failed');
            }
        } catch (\Exception $e) {
            parent::markTestSkipped('Docker server not running at ' . self::$baseUri . '. Run: docker-compose up -d');
        }
    }

    private function createCar(array $data): array
    {
        $response = self::$client->post('/car/create', ['json' => $data]);
        return json_decode($response->getBody()->getContents(), true);
    }

    // ============== POST /car/create ==============

    public function testCreateCarReturns201WithValidData(): void
    {
        $data = [
            'title' => 'BMW X5 2020',
            'description' => 'Отличное авто в отличном состоянии',
            'price' => 5500000.50,
            'photo_url' => 'https://example.com/bmw.jpg',
            'contacts' => '+7 999 123-45-67',
        ];

        $response = self::$client->post('/car/create', ['json' => $data]);

        $this->assertSame(201, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);

        $this->assertArrayHasKey('id', $body);
        $this->assertSame('BMW X5 2020', $body['title']);
        $this->assertSame(5500000.50, $body['price']);
        $this->assertNull($body['options']);
        $this->assertArrayHasKey('created_at', $body);
    }

    public function testCreateCarWithOptionsReturns201(): void
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

        $response = self::$client->post('/car/create', ['json' => $data]);

        $this->assertSame(201, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);

        $this->assertNotNull($body['options']);
        $this->assertSame('Toyota', $body['options']['brand']);
        $this->assertSame('Camry', $body['options']['model']);
        $this->assertSame(2022, $body['options']['year']);
        $this->assertSame('седан', $body['options']['body']);
        $this->assertSame(15000, $body['options']['mileage']);
    }

    public function testCreateCarWithNullOptionsReturns201(): void
    {
        $data = [
            'title' => 'Car With Null Options',
            'description' => 'Description',
            'price' => 500000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
            'options' => null,
        ];

        $response = self::$client->post('/car/create', ['json' => $data]);

        $this->assertSame(201, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertNull($body['options']);
    }

    public function testCreateCarWithoutOptionsFieldReturns201(): void
    {
        $data = [
            'title' => 'Car Without Options Field',
            'description' => 'Description',
            'price' => 500000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ];

        $response = self::$client->post('/car/create', ['json' => $data]);

        $this->assertSame(201, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertNull($body['options']);
    }

    public function testCreateCarReturns422WithMissingTitle(): void
    {
        $data = [
            'description' => 'Description',
            'price' => 1000000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ];

        try {
            self::$client->post('/car/create', ['json' => $data]);
            $this->fail('Expected ClientException');
        } catch (ClientException $e) {
            $this->assertSame(422, $e->getResponse()->getStatusCode());
            $body = json_decode($e->getResponse()->getBody()->getContents(), true);
            $this->assertArrayHasKey('errors', $body);
            $this->assertArrayHasKey('title', $body['errors']);
        }
    }

    public function testCreateCarReturns422WithEmptyTitle(): void
    {
        $data = [
            'title' => '',
            'description' => 'Description',
            'price' => 1000000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ];

        try {
            self::$client->post('/car/create', ['json' => $data]);
            $this->fail('Expected ClientException');
        } catch (ClientException $e) {
            $this->assertSame(422, $e->getResponse()->getStatusCode());
        }
    }

    public function testCreateCarReturns422WithNegativePrice(): void
    {
        $data = [
            'title' => 'Test Car',
            'description' => 'Description',
            'price' => -50000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ];

        try {
            self::$client->post('/car/create', ['json' => $data]);
            $this->fail('Expected ClientException');
        } catch (ClientException $e) {
            $this->assertSame(422, $e->getResponse()->getStatusCode());
            $body = json_decode($e->getResponse()->getBody()->getContents(), true);
            $this->assertArrayHasKey('price', $body['errors']);
        }
    }

    public function testCreateCarReturns422WithMissingOptionsFields(): void
    {
        $data = [
            'title' => 'Test Car',
            'description' => 'Description',
            'price' => 1000000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
            'options' => ['brand' => 'BMW'],
        ];

        try {
            self::$client->post('/car/create', ['json' => $data]);
            $this->fail('Expected ClientException');
        } catch (ClientException $e) {
            $this->assertSame(422, $e->getResponse()->getStatusCode());
            $body = json_decode($e->getResponse()->getBody()->getContents(), true);
            $this->assertArrayHasKey('options.model', $body['errors']);
            $this->assertArrayHasKey('options.year', $body['errors']);
            $this->assertArrayHasKey('options.body', $body['errors']);
            $this->assertArrayHasKey('options.mileage', $body['errors']);
        }
    }

    public function testCreateCarReturns422WithInvalidPhotoUrl(): void
    {
        $data = [
            'title' => 'Test Car',
            'description' => 'Description',
            'price' => 1000000,
            'photo_url' => 'not-a-valid-url',
            'contacts' => '+7 999 111-22-33',
        ];

        try {
            self::$client->post('/car/create', ['json' => $data]);
            $this->fail('Expected ClientException');
        } catch (ClientException $e) {
            $this->assertSame(422, $e->getResponse()->getStatusCode());
            $body = json_decode($e->getResponse()->getBody()->getContents(), true);
            $this->assertArrayHasKey('photo_url', $body['errors']);
        }
    }

    // ============== GET /car/{id} ==============

    public function testViewCarReturns200ForExistingCar(): void
    {
        $created = $this->createCar([
            'title' => 'View Test Car',
            'description' => 'Description',
            'price' => 500000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ]);

        $response = self::$client->get('/car/' . $created['id']);

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertSame('View Test Car', $body['title']);
    }

    public function testViewCarReturns404ForNonExistentCar(): void
    {
        try {
            self::$client->get('/car/99999');
            $this->fail('Expected ClientException');
        } catch (ClientException $e) {
            $this->assertSame(404, $e->getResponse()->getStatusCode());
        }
    }

    public function testViewCarWithOptionsReturns200(): void
    {
        $created = $this->createCar([
            'title' => 'View Car With Options',
            'description' => 'Description',
            'price' => 500000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
            'options' => [
                'brand' => 'BMW',
                'model' => 'X5',
                'year' => 2021,
                'body' => 'кроссовер',
                'mileage' => 30000,
            ],
        ]);

        $response = self::$client->get('/car/' . $created['id']);

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertNotNull($body['options']);
        $this->assertSame('BMW', $body['options']['brand']);
    }

    // ============== GET /car/list ==============

    public function testListCarsReturns200WithPagination(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $this->createCar([
                'title' => "List Test Car $i",
                'description' => "Description $i",
                'price' => $i * 100000,
                'photo_url' => "https://example.com/car$i.jpg",
                'contacts' => "+7 999 000-$i",
            ]);
        }

        $response = self::$client->get('/car/list');

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);

        $this->assertArrayHasKey('items', $body);
        $this->assertArrayHasKey('pagination', $body);
        $this->assertGreaterThanOrEqual(3, count($body['items']));
        $this->assertArrayHasKey('page', $body['pagination']);
        $this->assertArrayHasKey('totalCount', $body['pagination']);
    }

    public function testListCarsWithPageParameter(): void
    {
        $response = self::$client->get('/car/list?page=1');

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertSame(1, $body['pagination']['page']);
    }

    public function testListCarsEmptyReturns200(): void
    {
        $response = self::$client->get('/car/list?page=2');

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertSame([], $body['items']);
    }

    public function testListCarsReturns400WithInvalidPage(): void
    {
        try {
            self::$client->get('/car/list?page=abc');
            $this->fail('Expected ClientException');
        } catch (ClientException $e) {
            $this->assertSame(400, $e->getResponse()->getStatusCode());
            $body = json_decode($e->getResponse()->getBody()->getContents(), true);
            $this->assertArrayHasKey('errors', $body);
            $this->assertArrayHasKey('page', $body['errors']);
        }
    }

    public function testListCarsReturns400WithPageSizeTooLarge(): void
    {
        try {
            self::$client->get('/car/list?pageSize=500');
            $this->fail('Expected ClientException');
        } catch (ClientException $e) {
            $this->assertSame(400, $e->getResponse()->getStatusCode());
        }
    }

    public function testListCarsReturns400WithPageZero(): void
    {
        try {
            self::$client->get('/car/list?page=0');
            $this->fail('Expected ClientException');
        } catch (ClientException $e) {
            $this->assertSame(400, $e->getResponse()->getStatusCode());
        }
    }

    // ============== Edge Cases ==============

    public function testCreateCarReturns422WithTooLongTitle(): void
    {
        $data = [
            'title' => str_repeat('a', 300),
            'description' => 'Description',
            'price' => 1000000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ];

        try {
            self::$client->post('/car/create', ['json' => $data]);
            $this->fail('Expected ClientException');
        } catch (ClientException $e) {
            $this->assertSame(422, $e->getResponse()->getStatusCode());
            $body = json_decode($e->getResponse()->getBody()->getContents(), true);
            $this->assertArrayHasKey('title', $body['errors']);
        }
    }

    public function testCreateCarReturns422WithWhitespaceOnlyTitle(): void
    {
        $data = [
            'title' => '   ',
            'description' => 'Description',
            'price' => 1000000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ];

        try {
            self::$client->post('/car/create', ['json' => $data]);
            $this->fail('Expected ClientException');
        } catch (ClientException $e) {
            $this->assertSame(422, $e->getResponse()->getStatusCode());
        }
    }

    public function testCreateCarReturns422WithOptionsYearTooOld(): void
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
            self::$client->post('/car/create', ['json' => $data]);
            $this->fail('Expected ClientException');
        } catch (ClientException $e) {
            $this->assertSame(422, $e->getResponse()->getStatusCode());
            $body = json_decode($e->getResponse()->getBody()->getContents(), true);
            $this->assertArrayHasKey('options.year', $body['errors']);
        }
    }

    public function testCreateCarReturns422WithNegativeMileage(): void
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
                'year' => 2020,
                'body' => 'кроссовер',
                'mileage' => -1000,
            ],
        ];

        try {
            self::$client->post('/car/create', ['json' => $data]);
            $this->fail('Expected ClientException');
        } catch (ClientException $e) {
            $this->assertSame(422, $e->getResponse()->getStatusCode());
            $body = json_decode($e->getResponse()->getBody()->getContents(), true);
            $this->assertArrayHasKey('options.mileage', $body['errors']);
        }
    }

    public function testCreateCarReturns422WithPriceExceedingLimit(): void
    {
        $data = [
            'title' => 'Test Car',
            'description' => 'Description',
            'price' => 2000000000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ];

        try {
            self::$client->post('/car/create', ['json' => $data]);
            $this->fail('Expected ClientException');
        } catch (ClientException $e) {
            $this->assertSame(422, $e->getResponse()->getStatusCode());
            $body = json_decode($e->getResponse()->getBody()->getContents(), true);
            $this->assertArrayHasKey('price', $body['errors']);
        }
    }
}