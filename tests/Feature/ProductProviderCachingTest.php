<?php

use App\Catalogue\FakeStoreProductProvider;
use App\Catalogue\Product;
use Illuminate\Support\Facades\Http;

it('returns correctly hydrated products on a cache hit, not just a cache miss', function () {
    // Laravel's database cache store refuses to unserialize arbitrary
    // objects by default (cache.serializable_classes = false). Caching
    // hydrated Product DTOs directly would silently come back as
    // __PHP_Incomplete_Class on the second (cached) read — this exercises
    // that exact path with the real "database" cache driver.
    config(['cache.default' => 'database']);

    Http::fake([
        'fakestoreapi.com/*' => Http::response([
            ['id' => 1, 'title' => 'Widget', 'price' => 9.99, 'description' => 'A widget.', 'category' => 'tools', 'image' => '/w.jpg', 'rating' => ['rate' => 4.0, 'count' => 10]],
        ]),
    ]);

    $provider = new FakeStoreProductProvider;

    $first = $provider->all();
    $second = $provider->all(); // served from the database cache

    expect($second)->toHaveCount(1);
    expect($second->first())->toBeInstanceOf(Product::class);
    expect($second->first()->title)->toBe('Widget');
    expect($second->first()->title)->toBe($first->first()->title);

    Http::assertSentCount(1); // second call must not hit the network again
});
