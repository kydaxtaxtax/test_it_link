<?php

declare(strict_types=1);

namespace app\exceptions;

/**
 * Ошибка валидации входных данных.
 */
final class ValidationException extends \RuntimeException
{
    /**
     * @param array<string, list<string>> $errors
     */
    public function __construct(private readonly array $errors)
    {
        parent::__construct('Ошибки валидации');
    }

    /**
     * @return array<string, list<string>>
     */
    public function getFieldErrors(): array
    {
        return $this->errors;
    }
}
