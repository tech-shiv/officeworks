<?php

use App\Catalogue\Contracts\ProductProvider;
use Tests\Fakes\FakeProductProvider;

beforeEach(function () {
    $this->app->bind(ProductProvider::class, FakeProductProvider::class);
});

it('splits a category into top rated and additional products', function () {
    $response = $this->get('/categories/accessories');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('category')
        ->where('category', 'accessories')
        ->has('topRated')
        ->has('additional')
    );
});

it('matches category case-insensitively', function () {
    $response = $this->get('/categories/ACCESSORIES');

    $response->assertOk();
});

it('returns 404 for an unknown category', function () {
    $response = $this->get('/categories/unknown-category');

    $response->assertNotFound();
});
