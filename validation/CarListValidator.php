<?php

declare(strict_types=1);

namespace app\validation;

use Yii;

/**
 * Валидатор GET /car/list.
 * Валидирует параметры пагинации page и pageSize.
 */
final class CarListValidator extends AbstractValidator
{
    /**
     * @return array<string, array<string, mixed>>
     */
    protected function rules(): array
    {
        return [
            'page' => [
                'required' => false,
                'type' => 'integer',
                'min' => 1,
                'max' => 10000,
            ],
            'pageSize' => [
                'required' => false,
                'type' => 'integer',
                'min' => 1,
                'max' => 100,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, list<string>>
     */
    public function validate(array $data): array
    {
        $errors = [];

        $page = $data['page'] ?? null;
        $pageSize = $data['pageSize'] ?? null;

        if ($page !== null) {
            $pageNum = is_numeric($page) ? (int) $page : null;
            if ($pageNum === null || $pageNum < 1) {
                $errors['page'] = ['Параметр "page" должен быть числом от 1.'];
            } elseif ($pageNum > 10000) {
                $errors['page'] = ['Параметр "page" не может быть больше 10000.'];
            }
        }

        if ($pageSize !== null) {
            $sizeNum = is_numeric($pageSize) ? (int) $pageSize : null;
            if ($sizeNum === null || $sizeNum < 1) {
                $errors['pageSize'] = ['Параметр "pageSize" должен быть числом от 1.'];
            } elseif ($sizeNum > 100) {
                $errors['pageSize'] = ['Параметр "pageSize" не может быть больше 100.'];
            }
        }

        return $errors;
    }
}