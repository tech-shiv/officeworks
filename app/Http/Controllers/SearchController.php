<?php

namespace App\Http\Controllers;

use App\Catalogue\Contracts\ProductProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function index(Request $request, ProductProvider $products): Response
    {
        $query = trim((string) $request->query('q', ''));

        $results = $query === ''
            ? collect()
            : $products->all()->filter(
                fn ($product) => Str::contains($product->title, $query, ignoreCase: true)
                    || Str::contains($product->description, $query, ignoreCase: true)
            )->values();

        return Inertia::render('search', [
            'query' => $query,
            'results' => $results->map->toArray()->values(),
        ]);
    }
}
