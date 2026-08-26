<?php

declare(strict_types=1);

namespace app\validation;

/**
 * Интерфейс валидатора входных данных.
 * Валидатор возвращает структурированный список ошибок:
 *   ['field' => ['message1', 'message2'], ...]
 * Пустой массив означает, что данные валидны.
 */
interface ValidatorInterface
{
    /**
     * @param array<string, mixed> $data
     * @return array<string, list<string>>
     */
    public function validate(array $data): array;
}
