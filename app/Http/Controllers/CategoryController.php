<?php

namespace App\Http\Controllers;

use App\Catalogue\Contracts\ProductProvider;
use App\Catalogue\FeaturedProductSelector;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryController extends Controller
{
    public function show(string $category, ProductProvider $products): Response
    {
        $all = $products->all();

        $inCategory = $all->filter(
            fn ($product) => Str::lower(trim($product->category)) === Str::lower(trim($category))
        )->values();

        if ($inCategory->isEmpty()) {
            throw new NotFoundHttpException("Category \"{$category}\" was not found.");
        }

        $topRated = FeaturedProductSelector::byRating($inCategory)->take(3);
        $additional = $inCategory->reject(fn ($product) => $topRated->contains('id', $product->id))->values();

        return Inertia::render('category', [
            'category' => $category,
            'topRated' => $topRated->map->toArray()->values(),
            'additional' => $additional->map->toArray()->values(),
        ]);
    }
}
