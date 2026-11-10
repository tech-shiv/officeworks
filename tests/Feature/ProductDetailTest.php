<?php

use App\Catalogue\Contracts\ProductProvider;
use Tests\Fakes\FakeProductProvider;

beforeEach(function () {
    $this->app->bind(ProductProvider::class, FakeProductProvider::class);
});

it('shows the product details page for a valid id', function () {
    $response = $this->get('/products/1');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('product-detail')
        ->where('product.id', 1)
        ->where('product.title', 'Blue Backpack')
    );
});

it('returns 404 for an unknown product id', function () {
    $response = $this->get('/products/999');

    $response->assertNotFound();
});
