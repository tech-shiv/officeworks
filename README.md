# Officeworks Catalogue — Product Catalogue PoC

A proof-of-concept product catalogue: browse by category, search, view product
details, and submit an enquiry that emails a sales inbox. Built as a
foundation an entrepreneur can grow — not a throwaway prototype — so the
product data source, visual design, and email provider are all designed to
be swapped out later without touching business logic.

## Stack choice

**Laravel (PHP) + the Laravel React Starter Kit** (Inertia.js, React,
TypeScript, Tailwind CSS), SQLite for local development, Pest for testing.

Why: Inertia gives a single deployable app (server-rendered routing, React
for interactivity) with no separate API layer, no CORS, and no duplicate
auth/session handling between a SPA and an API — a smaller, simpler surface
for a 2-3 hour PoC that still scales to a real product. Laravel's
`Http` client, `Cache`, and `Mail` facades cover everything this PoC needs
(external API integration, caching, transactional email) without extra
packages.

## Architecture

```
app/Catalogue/
  Contracts/ProductProvider.php   — the swappable product data contract
  FakeStoreProductProvider.php    — Fake Store API implementation
  Product.php                     — plain DTO, not an Eloquent model
  FeaturedProductSelector.php     — "top rated" selection logic

app/Http/Controllers/             — thin controllers, one per route
app/Http/Requests/                — server-side form validation
app/Mail/EnquirySubmitted.php     — enquiry email

resources/js/pages/               — one Inertia page per route
resources/js/components/          — ProductCard, ProductGrid, EnquiryForm
resources/js/layouts/             — shared nav + search chrome
```

- **Product data source** is swappable: everything goes through the
  `ProductProvider` interface. Swapping Fake Store for a real database or
  another API means writing one new class and changing `PRODUCT_PROVIDER` in
  `.env` — no controller or frontend changes.
- **Email provider** is swappable via Laravel's own `MAIL_MAILER` config —
  there's no extra wrapper interface around `Mail`, since Laravel's mail
  system already _is_ the swappable abstraction (see DECISIONS.md).
- **Look and feel** lives entirely in `resources/js` behind a single
  `CatalogueLayout` + a small set of shared components, so a future redesign
  touches one layer, not the whole app.

## Setup (under 10 minutes)

Prerequisites: PHP 8.3+, Composer, Node 20+, npm.

```bash
cp .env.example .env
composer install
npm install
php artisan key:generate
touch database/database.sqlite
php artisan migrate
composer run dev
```

`composer run dev` runs the PHP server, Vite dev server, and queue listener
together. Visit the URL it prints (or `http://localhost:8000`).

If you're using [Laravel Herd](https://herd.laravel.com) and this folder is
parked under `~/Herd`, it's already served at `https://officeworks.test` —
no extra command needed.

## Configuration

| Variable                      | Purpose                                                              | Default                        |
| ----------------------------- | -------------------------------------------------------------------- | ------------------------------ |
| `PRODUCT_PROVIDER`            | Which `ProductProvider` implementation to bind                       | `fake_store`                   |
| `FAKE_STORE_BASE_URL`         | Fake Store API base URL                                              | `https://fakestoreapi.com`     |
| `FAKE_STORE_TIMEOUT`          | HTTP timeout (seconds) for the Fake Store client                     | `5`                            |
| `CATALOGUE_CACHE_TTL_MINUTES` | How long the product list is cached                                  | `10`                           |
| `FEATURED_PRODUCT_IDS`        | Comma-separated product ids curated as "top rated" for the home page | `1,2,3,4,5`                    |
| `SALES_INBOX_EMAIL`           | Where enquiry emails are sent                                        | `sales@example.test`           |
| `MAIL_MAILER`                 | Mail driver                                                          | `log` (no real account needed) |

With `MAIL_MAILER=log`, enquiry emails are written to
`storage/logs/laravel.log` instead of a real inbox — open that file after
submitting an enquiry to see it. Swap to `smtp`/`ses`/etc. in production.

## Testing

```bash
php artisan test       # Pest feature tests, one file per acceptance criterion
composer types:check   # PHPStan (larastan)
composer lint:check    # Pint
npm run types:check    # tsc --noEmit
npm run check          # frontend lint/format
```

Tests bind a `FakeProductProvider` in-memory stub over the `ProductProvider`
contract — no test makes a real network call to Fake Store.

## Known limitations / what's next

See `DECISIONS.md` for assumptions, simplifications, and what we'd build
next with another day.
