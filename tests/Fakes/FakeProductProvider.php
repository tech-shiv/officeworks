<?php

namespace Tests\Fakes;

use App\Catalogue\Contracts\ProductProvider;
use App\Catalogue\Product;
use Illuminate\Support\Collection;

class FakeProductProvider implements ProductProvider
{
    /**
     * @var Collection<int, Product>
     */
    private Collection $products;

    public function __construct()
    {
        $this->products = collect([
            new Product(1, 'Blue Backpack', 49.99, 'A sturdy blue backpack.', 'bags', '/img/1.jpg', 4.5, 120),
            new Product(2, 'Leather Wallet', 29.99, 'Slim leather wallet.', 'accessories', '/img/2.jpg', 4.8, 200),
            new Product(3, 'Running Shoes', 89.99, 'Lightweight running shoes.', 'footwear', '/img/3.jpg', 4.2, 80),
            new Product(4, 'Wool Sweater', 59.99, 'Warm wool sweater.', 'clothing', '/img/4.jpg', 3.9, 45),
            new Product(5, 'Sunglasses', 19.99, 'UV protection sunglasses.', 'accessories', '/img/5.jpg', 4.0, 60),
            new Product(6, 'Denim Jacket', 79.99, 'Classic denim jacket.', 'clothing', '/img/6.jpg', 4.6, 95),
            new Product(7, 'Canvas Tote', 15.99, 'Reusable canvas tote bag.', 'bags', '/img/7.jpg', 3.5, 30),
            new Product(8, 'Hiking Boots', 109.99, 'Durable hiking boots.', 'footwear', '/img/8.jpg', 4.9, 150),
        ]);
    }

    public function all(): Collection
    {
        return $this->products;
    }

    public function find(int $id): ?Product
    {
        return $this->products->firstWhere('id', $id);
    }
}
