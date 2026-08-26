<?php

declare(strict_types=1);

namespace app\validation;

/**
 * Валидатор тех. характеристик объявления (поле options).
 * Каждое поле имеет специфичные русские сообщения об ошибках.
 */
final class CarOptionsValidator extends AbstractValidator
{
    /**
     * @return array<string, array<string, mixed>>
     */
    protected function rules(): array
    {
        return [
            'brand' => [
                'required' => true,
                'type' => 'string',
                'min' => 1,
                'max' => 255,
            ],
            'model' => [
                'required' => true,
                'type' => 'string',
                'min' => 1,
                'max' => 255,
            ],
            'year' => [
                'required' => true,
                'type' => 'integer',
                'min' => 1886,
                'max' => (int) date('Y') + 1,
            ],
            'body' => [
                'required' => true,
                'type' => 'string',
                'max' => 100,
            ],
            'mileage' => [
                'required' => true,
                'type' => 'integer',
                'min' => 0,
                'max' => 10000000,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, list<string>>
     */
    public function validate(array $data): array
    {
        $errors = parent::validate($data);

        $brand = $data['brand'] ?? null;
        if ($brand !== null && is_string($brand) && trim($brand) === '') {
            $errors['brand'] = ['Марка автомобиля не может быть пустой.'];
        }

        $model = $data['model'] ?? null;
        if ($model !== null && is_string($model) && trim($model) === '') {
            $errors['model'] = ['Модель автомобиля не может быть пустой.'];
        }

        $year = $data['year'] ?? null;
        if ($year !== null && is_numeric($year)) {
            $yearInt = (int) $year;
            if ($yearInt < 1886) {
                $errors['year'] = ['Год выпуска не может быть раньше 1886 (год изобретения автомобиля).'];
            }
            if ($yearInt > (int) date('Y') + 1) {
                $errors['year'] = ['Год выпуска не может быть в будущем.'];
            }
        }

        $body = $data['body'] ?? null;
        if ($body !== null && is_string($body) && trim($body) === '') {
            $errors['body'] = ['Тип кузова не может быть пустым.'];
        }

        $mileage = $data['mileage'] ?? null;
        if ($mileage !== null && is_numeric($mileage)) {
            $mileageInt = (int) $mileage;
            if ($mileageInt < 0) {
                $errors['mileage'] = ['Пробег не может быть отрицательным.'];
            }
            if ($mileageInt > 10000000) {
                $errors['mileage'] = ['Пробег не может превышать 10 000 000 км.'];
            }
        }

        return $errors;
    }
}