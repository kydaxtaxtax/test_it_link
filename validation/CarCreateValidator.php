<?php

declare(strict_types=1);

namespace app\validation;

/**
 * Валидатор создания объявления (метод POST /car/create).
 */
final class CarCreateValidator extends AbstractValidator
{
    public function __construct(private readonly CarOptionsValidator $optionsValidator = new CarOptionsValidator())
    {
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, list<string>>
     */
    protected function rules(): array
    {
        return [
            'title' => ['required' => true, 'type' => 'string', 'min' => 1, 'max' => 255],
            'description' => ['required' => true, 'type' => 'string', 'min' => 1],
            'price' => ['required' => true, 'type' => 'number', 'min' => 0],
            'photo_url' => ['required' => true, 'type' => 'string', 'max' => 500],
            'contacts' => ['required' => true, 'type' => 'string', 'min' => 1, 'max' => 255],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, list<string>>
     */
    public function validate(array $data): array
    {
        $errors = parent::validate($data);

        $options = $data['options'] ?? null;

        if ($options === null) {
            return $errors;
        }

        if (!is_array($options)) {
            $errors['options'][] = 'Поле "options" должно быть объектом.';
            return $errors;
        }

        $optionErrors = $this->optionsValidator->validate($options);
        foreach ($optionErrors as $field => $messages) {
            $errors['options.' . $field] = $messages;
        }

        return $errors;
    }
}
