<?php

return [
    // Which ProductProvider implementation to bind. Swap to a new value +
    // add a case in CatalogueServiceProvider to change the data source.
    'provider' => env('PRODUCT_PROVIDER', 'fake_store'),

    'fake_store' => [
        'base_url' => env('FAKE_STORE_BASE_URL', 'https://fakestoreapi.com'),
        'timeout' => (int) env('FAKE_STORE_TIMEOUT', 5),
    ],

    'cache_ttl_minutes' => (int) env('CATALOGUE_CACHE_TTL_MINUTES', 10),

    // Owner-curated "top rated" product ids for the home page (comma-separated).
    // Falls back to the top 5 by rating when unset or fewer than 5 resolve.
    'featured_ids' => array_filter(array_map(
        'trim',
        explode(',', (string) env('FEATURED_PRODUCT_IDS', '1,2,3,4,5'))
    )),

    'sales_inbox' => env('SALES_INBOX_EMAIL', 'sales@example.test'),
];
