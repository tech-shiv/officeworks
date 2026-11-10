<?php

namespace App\Catalogue;

final readonly class Product
{
    public function __construct(
        public int $id,
        public string $title,
        public float $price,
        public string $description,
        public string $category,
        public string $image,
        public float $ratingRate,
        public int $ratingCount,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromFakeStore(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            title: (string) $data['title'],
            price: (float) $data['price'],
            description: (string) $data['description'],
            category: (string) $data['category'],
            image: (string) $data['image'],
            ratingRate: (float) ($data['rating']['rate'] ?? 0),
            ratingCount: (int) ($data['rating']['count'] ?? 0),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'price' => $this->price,
            'description' => $this->description,
            'category' => $this->category,
            'image' => $this->image,
            'rating' => [
                'rate' => $this->ratingRate,
                'count' => $this->ratingCount,
            ],
        ];
    }
}
