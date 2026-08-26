<?php

declare(strict_types=1);

namespace app\entities;

/**
 * Доменная сущность объявления автомобиля.
 */
final class CarEntity
{
    public function __construct(
        private ?int $id,
        private string $title,
        private string $description,
        private float $price,
        private string $photoUrl,
        private string $contacts,
        private ?string $createdAt = null,
        private ?CarOptionEntity $option = null,
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getPhotoUrl(): string
    {
        return $this->photoUrl;
    }

    public function getContacts(): string
    {
        return $this->contacts;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getOption(): ?CarOptionEntity
    {
        return $this->option;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'photo_url' => $this->photoUrl,
            'contacts' => $this->contacts,
            'created_at' => $this->createdAt,
            'options' => $this->option?->toArray(),
        ];
    }
}
