<?php

declare(strict_types=1);

namespace tests\unit\validation;

use app\validation\CarCreateValidator;
use app\validation\CarListValidator;
use app\validation\CarOptionsValidator;
use app\validation\CarViewValidator;
use PHPUnit\Framework\TestCase;

/**
 * Unit-тесты валидаторов с детальными сообщениями об ошибках.
 */
final class ValidatorTest extends TestCase
{
    private CarCreateValidator $createValidator;
    private CarOptionsValidator $optionsValidator;
    private CarViewValidator $viewValidator;
    private CarListValidator $listValidator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createValidator = new CarCreateValidator();
        $this->optionsValidator = new CarOptionsValidator();
        $this->viewValidator = new CarViewValidator();
        $this->listValidator = new CarListValidator();
    }

    // ============== CarCreateValidator ==============

    public function testValidCreateData(): void
    {
        $data = [
            'title' => 'BMW X5 2020 года',
            'description' => 'Отличное авто в отличном состоянии',
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
            'description' => 'Седан бизнес-класса',
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
        $this->assertSame('Поле "title" обязательно.', $errors['title'][0]);
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

    public function testTitleTooShort(): void
    {
        $data = [
            'title' => 'AB',
            'description' => 'Description',
            'price' => 1000000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ];

        $errors = $this->createValidator->validate($data);
        $this->assertArrayHasKey('title', $errors);
        $this->assertSame('Заголовок объявления должен содержать минимум 3 символа (без учёта пробелов).', $errors['title'][0]);
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

    public function testTitleWhitespaceOnly(): void
    {
        $data = [
            'title' => '   ',
            'description' => 'Description',
            'price' => 1000000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ];

        $errors = $this->createValidator->validate($data);
        $this->assertArrayHasKey('title', $errors);
        $this->assertSame('Заголовок объявления не может состоять только из пробелов.', $errors['title'][0]);
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
        $this->assertSame('Цена не может быть отрицательной.', $errors['price'][0]);
    }

    public function testPriceTooHigh(): void
    {
        $data = [
            'title' => 'Test Car',
            'description' => 'Description',
            'price' => 2000000000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ];

        $errors = $this->createValidator->validate($data);
        $this->assertArrayHasKey('price', $errors);
        $this->assertSame('Цена не может превышать 1 000 000 000 руб.', $errors['price'][0]);
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

    public function testInvalidPhotoUrl(): void
    {
        $data = [
            'title' => 'Test Car',
            'description' => 'Description',
            'price' => 1000000,
            'photo_url' => 'not-a-valid-url',
            'contacts' => '+7 999 111-22-33',
        ];

        $errors = $this->createValidator->validate($data);
        $this->assertArrayHasKey('photo_url', $errors);
        $this->assertSame('Укажите корректную ссылку на фото (URL).', $errors['photo_url'][0]);
    }

    public function testPhotoUrlWithoutProtocol(): void
    {
        $data = [
            'title' => 'Test Car',
            'description' => 'Description',
            'price' => 1000000,
            'photo_url' => 'ftp://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ];

        $errors = $this->createValidator->validate($data);
        $this->assertArrayHasKey('photo_url', $errors);
        $this->assertSame('Ссылка на фото должна начинаться с http:// или https://.', $errors['photo_url'][0]);
    }

    public function testContactsWhitespaceOnly(): void
    {
        $data = [
            'title' => 'Test Car',
            'description' => 'Description',
            'price' => 1000000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '   ',
        ];

        $errors = $this->createValidator->validate($data);
        $this->assertArrayHasKey('contacts', $errors);
        $this->assertSame('Контактные данные не могут быть пустыми.', $errors['contacts'][0]);
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

    public function testMissingDescription(): void
    {
        $data = [
            'title' => 'Test Car',
            'price' => 1000000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
        ];

        $errors = $this->createValidator->validate($data);
        $this->assertArrayHasKey('description', $errors);
        $this->assertSame('Поле "description" обязательно.', $errors['description'][0]);
    }

    public function testMissingContacts(): void
    {
        $data = [
            'title' => 'Test Car',
            'description' => 'Description',
            'price' => 1000000,
            'photo_url' => 'https://example.com/photo.jpg',
        ];

        $errors = $this->createValidator->validate($data);
        $this->assertArrayHasKey('contacts', $errors);
        $this->assertSame('Поле "contacts" обязательно.', $errors['contacts'][0]);
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
        $this->assertSame('Технические характеристики должны быть объектом.', $errors['options'][0]);
    }

    public function testOptionsEmptyObject(): void
    {
        $data = [
            'title' => 'Test Car',
            'description' => 'Description',
            'price' => 1000000,
            'photo_url' => 'https://example.com/photo.jpg',
            'contacts' => '+7 999 111-22-33',
            'options' => [],
        ];

        $errors = $this->createValidator->validate($data);
        $this->assertArrayHasKey('options', $errors);
        $this->assertSame('Технические характеристики не могут быть пустым объектом.', $errors['options'][0]);
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

    public function testMissingOptionsFields(): void
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

        $errors = $this->createValidator->validate($data);
        $this->assertArrayHasKey('options.model', $errors);
        $this->assertArrayHasKey('options.year', $errors);
        $this->assertArrayHasKey('options.body', $errors);
        $this->assertArrayHasKey('options.mileage', $errors);
    }

    // ============== CarOptionsValidator ==============

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

    public function testOptionsYearTooOld(): void
    {
        $data = [
            'brand' => 'BMW',
            'model' => 'X5',
            'year' => 1800,
            'body' => 'кроссовер',
            'mileage' => 100000,
        ];

        $errors = $this->optionsValidator->validate($data);
        $this->assertArrayHasKey('year', $errors);
        $this->assertSame('Год выпуска не может быть раньше 1886 (год изобретения автомобиля).', $errors['year'][0]);
    }

    public function testOptionsYearInFuture(): void
    {
        $data = [
            'brand' => 'BMW',
            'model' => 'X5',
            'year' => (int) date('Y') + 10,
            'body' => 'кроссовер',
            'mileage' => 100000,
        ];

        $errors = $this->optionsValidator->validate($data);
        $this->assertArrayHasKey('year', $errors);
        $this->assertSame('Год выпуска не может быть в будущем.', $errors['year'][0]);
    }

    public function testOptionsNegativeMileage(): void
    {
        $data = [
            'brand' => 'BMW',
            'model' => 'X5',
            'year' => 2020,
            'body' => 'кроссовер',
            'mileage' => -1000,
        ];

        $errors = $this->optionsValidator->validate($data);
        $this->assertArrayHasKey('mileage', $errors);
        $this->assertSame('Пробег не может быть отрицательным.', $errors['mileage'][0]);
    }

    public function testOptionsMileageTooHigh(): void
    {
        $data = [
            'brand' => 'BMW',
            'model' => 'X5',
            'year' => 2020,
            'body' => 'кроссовер',
            'mileage' => 20000000,
        ];

        $errors = $this->optionsValidator->validate($data);
        $this->assertArrayHasKey('mileage', $errors);
        $this->assertSame('Пробег не может превышать 10 000 000 км.', $errors['mileage'][0]);
    }

    public function testOptionsEmptyBrand(): void
    {
        $data = [
            'brand' => '',
            'model' => 'X5',
            'year' => 2020,
            'body' => 'кроссовер',
            'mileage' => 100000,
        ];

        $errors = $this->optionsValidator->validate($data);
        $this->assertArrayHasKey('brand', $errors);
        $this->assertSame('Марка автомобиля не может быть пустой.', $errors['brand'][0]);
    }

    public function testOptionsEmptyModel(): void
    {
        $data = [
            'brand' => 'BMW',
            'model' => '  ',
            'year' => 2020,
            'body' => 'кроссовер',
            'mileage' => 100000,
        ];

        $errors = $this->optionsValidator->validate($data);
        $this->assertArrayHasKey('model', $errors);
        $this->assertSame('Модель автомобиля не может быть пустой.', $errors['model'][0]);
    }

    public function testOptionsEmptyBody(): void
    {
        $data = [
            'brand' => 'BMW',
            'model' => 'X5',
            'year' => 2020,
            'body' => '',
            'mileage' => 100000,
        ];

        $errors = $this->optionsValidator->validate($data);
        $this->assertArrayHasKey('body', $errors);
        $this->assertSame('Тип кузова не может быть пустым.', $errors['body'][0]);
    }

    // ============== CarViewValidator ==============

    public function testViewValidatorValidId(): void
    {
        $errors = $this->viewValidator->validate(['id' => 123]);
        $this->assertSame([], $errors);
    }

    public function testViewValidatorStringNumericId(): void
    {
        $errors = $this->viewValidator->validate(['id' => '456']);
        $this->assertSame([], $errors);
    }

    public function testViewValidatorMissingId(): void
    {
        $errors = $this->viewValidator->validate([]);
        $this->assertArrayHasKey('id', $errors);
        $this->assertSame('Объявление с указанным идентификатором не существует.', $errors['id'][0]);
    }

    public function testViewValidatorZeroId(): void
    {
        $errors = $this->viewValidator->validate(['id' => 0]);
        $this->assertArrayHasKey('id', $errors);
        $this->assertSame('Объявление с указанным идентификатором не существует.', $errors['id'][0]);
    }

    public function testViewValidatorNegativeId(): void
    {
        $errors = $this->viewValidator->validate(['id' => -5]);
        $this->assertArrayHasKey('id', $errors);
        $this->assertSame('Объявление с указанным идентификатором не существует.', $errors['id'][0]);
    }

    public function testViewValidatorNonNumericId(): void
    {
        $errors = $this->viewValidator->validate(['id' => 'abc']);
        $this->assertArrayHasKey('id', $errors);
        $this->assertSame('Объявление с указанным идентификатором не существует.', $errors['id'][0]);
    }

    // ============== CarListValidator ==============

    public function testListValidatorDefaultIsValid(): void
    {
        $errors = $this->listValidator->validate([]);
        $this->assertSame([], $errors);
    }

    public function testListValidatorValidPage(): void
    {
        $errors = $this->listValidator->validate(['page' => 5]);
        $this->assertSame([], $errors);
    }

    public function testListValidatorValidPageSize(): void
    {
        $errors = $this->listValidator->validate(['pageSize' => 50]);
        $this->assertSame([], $errors);
    }

    public function testListValidatorBothParams(): void
    {
        $errors = $this->listValidator->validate(['page' => 2, 'pageSize' => 25]);
        $this->assertSame([], $errors);
    }

    public function testListValidatorPageZero(): void
    {
        $errors = $this->listValidator->validate(['page' => 0]);
        $this->assertArrayHasKey('page', $errors);
        $this->assertSame('Параметр "page" должен быть числом от 1.', $errors['page'][0]);
    }

    public function testListValidatorPageTooLarge(): void
    {
        $errors = $this->listValidator->validate(['page' => 50000]);
        $this->assertArrayHasKey('page', $errors);
        $this->assertSame('Параметр "page" не может быть больше 10000.', $errors['page'][0]);
    }

    public function testListValidatorPageNonNumeric(): void
    {
        $errors = $this->listValidator->validate(['page' => 'abc']);
        $this->assertArrayHasKey('page', $errors);
        $this->assertSame('Параметр "page" должен быть числом от 1.', $errors['page'][0]);
    }

    public function testListValidatorPageSizeZero(): void
    {
        $errors = $this->listValidator->validate(['pageSize' => 0]);
        $this->assertArrayHasKey('pageSize', $errors);
        $this->assertSame('Параметр "pageSize" должен быть числом от 1.', $errors['pageSize'][0]);
    }

    public function testListValidatorPageSizeTooLarge(): void
    {
        $errors = $this->listValidator->validate(['pageSize' => 500]);
        $this->assertArrayHasKey('pageSize', $errors);
        $this->assertSame('Параметр "pageSize" не может быть больше 100.', $errors['pageSize'][0]);
    }

    public function testListValidatorPageSizeNonNumeric(): void
    {
        $errors = $this->listValidator->validate(['pageSize' => 'xyz']);
        $this->assertArrayHasKey('pageSize', $errors);
        $this->assertSame('Параметр "pageSize" должен быть числом от 1.', $errors['pageSize'][0]);
    }
}