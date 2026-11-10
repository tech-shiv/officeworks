<?php

namespace App\Http\Controllers;

use App\Catalogue\Contracts\ProductProvider;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductController extends Controller
{
    public function show(int $id, ProductProvider $products): Response
    {
        $product = $products->find($id);

        if (! $product) {
            throw new NotFoundHttpException("Product {$id} was not found.");
        }

        return Inertia::render('product-detail', [
            'product' => $product->toArray(),
        ]);
    }
}
