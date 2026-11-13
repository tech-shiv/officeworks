<?php

namespace App\Catalogue;

use App\Catalogue\Contracts\ProductProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FakeStoreProductProvider implements ProductProvider
{
    public function all(): Collection
    {
        // Cache the raw (plain-array) API payload, not hydrated Product
        // objects: Laravel's cache stores refuse to unserialize arbitrary
        // objects by default (`cache.serializable_classes`), so caching DTOs
        // directly silently returns __PHP_Incomplete_Class on the next read.
        $items = Cache::remember(
            'catalogue.products',
            now()->addMinutes((int) config('catalogue.cache_ttl_minutes')),
            function (): array {
                $baseUrl = config('catalogue.fake_store.base_url');
                $timeout = config('catalogue.fake_store.timeout');

                try {
                    $response = Http::timeout($timeout)->get("{$baseUrl}/products");
                } catch (\Throwable $e) {
                    Log::warning('Fake Store API request failed', ['error' => $e->getMessage()]);

                    throw new HttpException(503, 'The product catalogue is temporarily unavailable.', $e);
                }

                if ($response->failed()) {
                    Log::warning('Fake Store API returned an error response', ['status' => $response->status()]);

                    throw new HttpException(503, 'The product catalogue is temporarily unavailable.');
                }

                /** @var list<array<string, mixed>> */
                return $response->json();
            }
        );

        return collect($items)->map(fn (array $item) => Product::fromFakeStore($item));
    }

    public function find(int $id): ?Product
    {
        return $this->all()->firstWhere('id', $id);
    }
}
