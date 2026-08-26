<?php

declare(strict_types=1);

namespace app\validation;

/**
 * Базовый валидатор с общей реализацией проверки правил.
 *
 * Правила описываются в формате:
 *   field => [
 *       'required' => bool,
 *       'type'     => 'string'|'integer'|'number'|'array',
 *       'nullable' => bool,          // разрешено null (опциональное поле)
 *       'min'      => int|float,     // минимальное значение / длина
 *       'max'      => int|float,     // максимальное значение / длина
 *   ]
 */
abstract class AbstractValidator implements ValidatorInterface
{
    /**
     * @return array<string, array<string, mixed>>
     */
    abstract protected function rules(): array;

    /**
     * @param array<string, mixed> $data
     * @return array<string, list<string>>
     */
    public function validate(array $data): array
    {
        $errors = [];

        foreach ($this->rules() as $field => $rule) {
            $exists = array_key_exists($field, $data);
            $value = $exists ? $data[$field] : null;

            if ($value === null && ($rule['nullable'] ?? false)) {
                continue;
            }

            if (!$exists || $value === null || $value === '') {
                if ($rule['required'] ?? false) {
                    $errors[$field][] = sprintf('Поле "%s" обязательно.', $field);
                }
                continue;
            }

            $this->validateType($field, $value, $rule, $errors);
            $this->validateRange($field, $value, $rule, $errors);
        }

        return $errors;
    }

    /**
     * @param array<string, mixed> $rule
     * @param array<string, list<string>> $errors
     */
    protected function validateType(string $field, mixed $value, array $rule, array &$errors): void
    {
        $type = $rule['type'] ?? 'string';

        $ok = match ($type) {
            'string' => is_string($value),
            'integer' => is_int($value) || (is_string($value) && ctype_digit($value)),
            'number' => is_numeric($value),
            'array' => is_array($value),
            default => true,
        };

        if (!$ok) {
            $errors[$field][] = sprintf('Поле "%s" должно быть типа %s.', $field, $type);
        }
    }

    /**
     * @param array<string, mixed> $rule
     * @param array<string, list<string>> $errors
     */
    protected function validateRange(string $field, mixed $value, array $rule, array &$errors): void
    {
        $type = $rule['type'] ?? 'string';

        if ($type === 'array') {
            $length = is_array($value) ? count($value) : 0;
            if (isset($rule['min']) && $length < $rule['min']) {
                $errors[$field][] = sprintf('Поле "%s" должно содержать минимум %d элементов.', $field, (int) $rule['min']);
            }
            if (isset($rule['max']) && $length > $rule['max']) {
                $errors[$field][] = sprintf('Поле "%s" должно содержать максимум %d элементов.', $field, (int) $rule['max']);
            }
            return;
        }

        if ($type === 'string') {
            if (isset($rule['min']) || isset($rule['max'])) {
                $length = mb_strlen((string) $value, 'UTF-8');
                if (isset($rule['min']) && $length < $rule['min']) {
                    $errors[$field][] = sprintf('Поле "%s" должно содержать не меньше %d символов.', $field, (int) $rule['min']);
                }
                if (isset($rule['max']) && $length > $rule['max']) {
                    $errors[$field][] = sprintf('Поле "%s" должно содержать не больше %d символов.', $field, (int) $rule['max']);
                }
            }
            return;
        }

        if (isset($rule['min']) && (float) $value < (float) $rule['min']) {
            $errors[$field][] = sprintf('Поле "%s" должно быть не меньше %s.', $field, (string) $rule['min']);
        }

        if (isset($rule['max']) && (float) $value > (float) $rule['max']) {
            $errors[$field][] = sprintf('Поле "%s" должно быть не больше %s.', $field, (string) $rule['max']);
        }
    }

    /**
     * @param array<string, list<string>> $errors
     * @return array<string, list<string>>
     */
    protected function wrap(array $errors, string $prefix = ''): array
    {
        if ($prefix === '' || $errors === []) {
            return $errors;
        }

        $result = [];
        foreach ($errors as $key => $messages) {
            $result[$prefix . '.' . $key] = $messages;
        }

        return $result;
    }
}
