<?php

use App\Catalogue\Contracts\ProductProvider;
use Tests\Fakes\FakeProductProvider;

beforeEach(function () {
    $this->app->bind(ProductProvider::class, FakeProductProvider::class);
});

it('returns only products matching the search term in the title', function () {
    $response = $this->get('/search?q=Wallet');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('search')
        ->has('results', 1)
        ->where('results.0.title', 'Leather Wallet')
    );
});

it('matches case-insensitively against the description too', function () {
    $response = $this->get('/search?q=lightweight');

    $response->assertInertia(fn ($page) => $page->has('results', 1));
});

it('returns no results for an unmatched term without erroring', function () {
    $response = $this->get('/search?q=zzznomatch');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->has('results', 0));
});

it('returns an empty result set for an empty query', function () {
    $response = $this->get('/search?q=');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->has('results', 0));
});
