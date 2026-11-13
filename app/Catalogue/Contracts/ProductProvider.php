<?php

namespace App\Catalogue\Contracts;

use App\Catalogue\Product;
use Illuminate\Support\Collection;

interface ProductProvider
{
    /**
     * @return Collection<int, Product>
     */
    public function all(): Collection;

    public function find(int $id): ?Product;
}
