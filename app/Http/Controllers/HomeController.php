<?php

namespace App\Http\Controllers;

use App\Catalogue\Contracts\ProductProvider;
use App\Catalogue\FeaturedProductSelector;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function index(ProductProvider $products): Response
    {
        $featured = FeaturedProductSelector::select($products->all());

        return Inertia::render('home', [
            'featured' => $featured->map->toArray()->values(),
        ]);
    }
}
