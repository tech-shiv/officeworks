<?php

use App\Catalogue\Contracts\ProductProvider;
use Tests\Fakes\FakeProductProvider;

beforeEach(function () {
    $this->app->bind(ProductProvider::class, FakeProductProvider::class);
});

it('shows exactly 5 featured products on the home page', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('home')
        ->has('featured', 5)
    );
});

it('features the configured product ids in order', function () {
    config(['catalogue.featured_ids' => ['2', '4', '1', '3', '5']]);

    $response = $this->get('/');

    $response->assertInertia(fn ($page) => $page
        ->where('featured.0.id', 2)
        ->where('featured.1.id', 4)
    );
});

it('falls back to top rated by rating when fewer than 5 ids are configured', function () {
    config(['catalogue.featured_ids' => ['1']]);

    $response = $this->get('/');

    $response->assertInertia(fn ($page) => $page->has('featured', 5));
});
