<?php

declare(strict_types=1);

namespace app\validation;

/**
 * Валидатор тех. характеристик объявления (поле options).
 * Все поля обязательны, если объект options передан.
 */
final class CarOptionsValidator extends AbstractValidator
{
    /**
     * @param array<string, mixed> $data
     * @return array<string, list<string>>
     */
    protected function rules(): array
    {
        return [
            'brand' => ['required' => true, 'type' => 'string', 'max' => 255],
            'model' => ['required' => true, 'type' => 'string', 'max' => 255],
            'year' => ['required' => true, 'type' => 'integer', 'min' => 1886, 'max' => (int) date('Y') + 1],
            'body' => ['required' => true, 'type' => 'string', 'max' => 100],
            'mileage' => ['required' => true, 'type' => 'integer', 'min' => 0, 'max' => 10000000],
        ];
    }
}
