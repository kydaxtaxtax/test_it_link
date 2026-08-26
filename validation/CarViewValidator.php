<?php

declare(strict_types=1);

namespace app\validation;

use Yii;

/**
 * Валидатор GET /car/{id}.
 * Валидирует, что id — положительное целое число.
 */
final class CarViewValidator extends AbstractValidator
{
    /**
     * @return array<string, array<string, mixed>>
     */
    protected function rules(): array
    {
        return [
            'id' => [
                'required' => true,
                'type' => 'integer',
                'min' => 1,
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

        if (!isset($errors['id']) && isset($data['id'])) {
            $id = $data['id'];

            if (is_string($id) && !ctype_digit($id) && !is_numeric($id)) {
                $errors['id'][] = 'ID объявления должен быть числом.';
            } elseif (is_numeric($id) && (int) $id < 1) {
                $errors['id'][] = 'ID объявления должен быть положительным числом.';
            }
        }

        if (isset($errors['id'])) {
            $errors['id'] = ['Объявление с указанным идентификатором не существует.'];
        }

        return $errors;
    }
}