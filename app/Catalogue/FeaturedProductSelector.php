<?php

namespace App\Catalogue;

use Illuminate\Support\Collection;

class FeaturedProductSelector
{
    /**
     * Resolve the 5 "top rated" products for the home page.
     *
     * Prefers the owner-curated `catalogue.featured_ids` list; falls back to
     * the top 5 by rating (rate desc, count as tie-breaker) when that list
     * is unset or fewer than 5 of the configured ids resolve to a product.
     *
     * @param  Collection<int, Product>  $products
     * @return Collection<int, Product>
     */
    public static function select(Collection $products, int $count = 5): Collection
    {
        $ids = array_map('intval', config('catalogue.featured_ids', []));

        $curated = $products->whereIn('id', $ids)->sortBy(fn (Product $p) => array_search($p->id, $ids))->values();

        if ($curated->count() >= $count) {
            return $curated->take($count);
        }

        return self::byRating($products)->take($count);
    }

    /**
     * @param  Collection<int, Product>  $products
     * @return Collection<int, Product>
     */
    public static function byRating(Collection $products): Collection
    {
        return $products
            ->sortByDesc(fn (Product $p) => [$p->ratingRate, $p->ratingCount])
            ->values();
    }
}
