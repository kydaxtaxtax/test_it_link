<?php

declare(strict_types=1);

namespace tests\unit\validation;

use app\validation\CarCreateValidator;
use app\validation\CarOptionsValidator;
use PHPUnit\Framework\TestCase;

/**
 * Unit-тесты валидаторов.
 */
final class ValidatorTest extends TestCase
{
    private CarCreateValidator $createValidator;
    private CarOptionsValidator $optionsValidator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createValidator = new CarCreateValidator();
        $this->optionsValidator = new CarOptionsValidator();
    }

    public function testValidCreateData(): void
    {
        $data = [
            'title' => 'BMW X5',
            'description' => 'Отличное авто',
            'price' => 5500000.50,
            'photo_url' => 'https://example.com/bmw.jpg',
            'contacts' => '+7 999 123-45-67',
        ];

        $errors = $this->createValidator->validate($data);
        $this->assertSame([], $errors);
    }

    public function testValidCreateDataWithOptions(): void
    {
        $data = [
            'title' => 'Toyota Camry',
            'description' => 'Седан',
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

        $errors = $this->createValidator->validate($data);
        $this->assertSame([], $errors);
    }

    public function testMissingTitle(): void
    {
        $data = [
            'description' => 'Description',
            'price' => 1000000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ];

        $errors = $this->createValidator->validate($data);
        $this->assertArrayHasKey('title', $errors);
    }

    public function testEmptyTitle(): void
    {
        $data = [
            'title' => '',
            'description' => 'Description',
            'price' => 1000000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ];

        $errors = $this->createValidator->validate($data);
        $this->assertArrayHasKey('title', $errors);
    }

    public function testNegativePrice(): void
    {
        $data = [
            'title' => 'Test Car',
            'description' => 'Description',
            'price' => -50000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ];

        $errors = $this->createValidator->validate($data);
        $this->assertArrayHasKey('price', $errors);
    }

    public function testZeroPriceIsValid(): void
    {
        $data = [
            'title' => 'Free Car',
            'description' => 'Description',
            'price' => 0,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ];

        $errors = $this->createValidator->validate($data);
        $this->assertArrayNotHasKey('price', $errors);
    }

    public function testNonNumericPrice(): void
    {
        $data = [
            'title' => 'Test Car',
            'description' => 'Description',
            'price' => 'not-a-number',
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ];

        $errors = $this->createValidator->validate($data);
        $this->assertArrayHasKey('price', $errors);
    }

    public function testMissingRequiredOptionField(): void
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
            ],
        ];

        $errors = $this->createValidator->validate($data);
        $this->assertArrayHasKey('options.year', $errors);
        $this->assertArrayHasKey('options.body', $errors);
        $this->assertArrayHasKey('options.mileage', $errors);
    }

    public function testOptionsYearTooOld(): void
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

        $errors = $this->createValidator->validate($data);
        $this->assertArrayHasKey('options.year', $errors);
    }

    public function testOptionsYearInFuture(): void
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
                'year' => (int) date('Y') + 10,
                'body' => 'кроссовер',
                'mileage' => 100000,
            ],
        ];

        $errors = $this->createValidator->validate($data);
        $this->assertArrayHasKey('options.year', $errors);
    }

    public function testNegativeMileage(): void
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

        $errors = $this->createValidator->validate($data);
        $this->assertArrayHasKey('options.mileage', $errors);
    }

    public function testOptionsFieldNotAnObject(): void
    {
        $data = [
            'title' => 'Test Car',
            'description' => 'Description',
            'price' => 1000000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
            'options' => 'not-an-array',
        ];

        $errors = $this->createValidator->validate($data);
        $this->assertArrayHasKey('options', $errors);
    }

    public function testNullOptionsIsValid(): void
    {
        $data = [
            'title' => 'Test Car',
            'description' => 'Description',
            'price' => 1000000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
            'options' => null,
        ];

        $errors = $this->createValidator->validate($data);
        $this->assertArrayNotHasKey('options', $errors);
    }

    public function testMissingOptionsFieldIsValid(): void
    {
        $data = [
            'title' => 'Test Car',
            'description' => 'Description',
            'price' => 1000000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ];

        $errors = $this->createValidator->validate($data);
        $this->assertArrayNotHasKey('options', $errors);
    }

    public function testOptionsValidatorValid(): void
    {
        $data = [
            'brand' => 'BMW',
            'model' => 'X5',
            'year' => 2022,
            'body' => 'кроссовер',
            'mileage' => 50000,
        ];

        $errors = $this->optionsValidator->validate($data);
        $this->assertSame([], $errors);
    }

    public function testOptionsValidatorMissingAllFields(): void
    {
        $data = [];

        $errors = $this->optionsValidator->validate($data);
        $this->assertArrayHasKey('brand', $errors);
        $this->assertArrayHasKey('model', $errors);
        $this->assertArrayHasKey('year', $errors);
        $this->assertArrayHasKey('body', $errors);
        $this->assertArrayHasKey('mileage', $errors);
    }

    public function testTitleTooLong(): void
    {
        $data = [
            'title' => str_repeat('a', 300),
            'description' => 'Description',
            'price' => 1000000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ];

        $errors = $this->createValidator->validate($data);
        $this->assertArrayHasKey('title', $errors);
    }

    public function testContactsTooLong(): void
    {
        $data = [
            'title' => 'Test Car',
            'description' => 'Description',
            'price' => 1000000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => str_repeat('x', 300),
        ];

        $errors = $this->createValidator->validate($data);
        $this->assertArrayHasKey('contacts', $errors);
    }
}