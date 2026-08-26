<?php

declare(strict_types=1);

namespace app\validation;

/**
 * Валидатор создания объявления (POST /car/create).
 * Каждое поле имеет специфичные русские сообщения об ошибках.
 */
final class CarCreateValidator extends AbstractValidator
{
    public function __construct(
        private readonly CarOptionsValidator $optionsValidator = new CarOptionsValidator(),
    ) {
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function rules(): array
    {
        return [
            'title' => [
                'required' => true,
                'type' => 'string',
                'min' => 1,
                'max' => 255,
            ],
            'description' => [
                'required' => true,
                'type' => 'string',
                'min' => 1,
            ],
            'price' => [
                'required' => true,
                'type' => 'number',
                'min' => 0,
            ],
            'photo_url' => [
                'required' => true,
                'type' => 'string',
                'max' => 500,
            ],
            'contacts' => [
                'required' => true,
                'type' => 'string',
                'min' => 1,
                'max' => 255,
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

        $title = $data['title'] ?? null;
        if ($title !== null && $title !== '' && is_string($title)) {
            if (trim($title) === '') {
                $errors['title'] = ['Заголовок объявления не может состоять только из пробелов.'];
            } elseif (mb_strlen(trim($title), 'UTF-8') < 3) {
                $errors['title'] = ['Заголовок объявления должен содержать минимум 3 символа (без учёта пробелов).'];
            }
        }

        $price = $data['price'] ?? null;
        if ($price !== null && is_numeric($price)) {
            if ((float) $price > 1000000000) {
                $errors['price'] = ['Цена не может превышать 1 000 000 000 руб.'];
            }
            if ((float) $price < 0) {
                $errors['price'] = ['Цена не может быть отрицательной.'];
            }
        }

        $photoUrl = $data['photo_url'] ?? null;
        if ($photoUrl !== null && is_string($photoUrl) && $photoUrl !== '') {
            if (!filter_var($photoUrl, FILTER_VALIDATE_URL)) {
                $errors['photo_url'] = ['Укажите корректную ссылку на фото (URL).'];
            } elseif (!str_starts_with($photoUrl, 'http://') && !str_starts_with($photoUrl, 'https://')) {
                $errors['photo_url'] = ['Ссылка на фото должна начинаться с http:// или https://.'];
            }
        }

        $contacts = $data['contacts'] ?? null;
        if ($contacts !== null && is_string($contacts) && trim($contacts) === '') {
            $errors['contacts'] = ['Контактные данные не могут быть пустыми.'];
        }

        $options = $data['options'] ?? null;
        if ($options !== null && is_array($options)) {
            if (empty($options)) {
                $errors['options'] = ['Технические характеристики не могут быть пустым объектом.'];
                return $errors;
            }

            $optionErrors = $this->optionsValidator->validate($options);
            foreach ($optionErrors as $field => $messages) {
                $errors['options.' . $field] = $messages;
            }
        } elseif ($options !== null && !is_array($options)) {
            $errors['options'] = ['Технические характеристики должны быть объектом.'];
        }

        return $errors;
    }
}