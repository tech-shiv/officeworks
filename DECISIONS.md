# Decisions & Assumptions

## Assumptions made where the brief was unclear

| Area                                                             | Assumption                                                                                                                                                                                           | Why                                                                                                                                                                                                                                                                                                                                                                                     |
| ---------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| How the featured 5 are chosen                                    | Owner-curated via `FEATURED_PRODUCT_IDS` env (ids in display order); falls back to the top 5 by `rating.rate` (rating count as tie-breaker) if fewer than 5 configured ids resolve to a real product | The acceptance criterion says "GIVEN I have chosen 5 products to be top rated" — that's curation language, not "sort by rating." A pure algorithmic sort would satisfy the letter of the criterion but not the entrepreneur's actual intent of hand-picking their 5 best products. The rating-based fallback keeps the home page non-empty if the owner hasn't configured anything yet. |
| Top-rated vs "additional" split within a category                | Top 3 by `rating.rate` (count as tie-breaker) = top-rated section; everything else in the category = additional                                                                                      | The criterion doesn't specify a count for the category's top-rated section; 3 felt like a reasonable "featured within category" size without crowding out the "additional" section on small categories.                                                                                                                                                                                 |
| Invalid category slug                                            | HTTP 404                                                                                                                                                                                             | No acceptance criterion covers this; 404 is the standard, honest response for "this resource doesn't exist" rather than silently showing an empty page.                                                                                                                                                                                                                                 |
| Invalid product id                                               | HTTP 404                                                                                                                                                                                             | Same reasoning. Fake Store's API itself returns `200` with a `null` body for unknown ids, which the provider treats as not-found rather than propagating a confusing null.                                                                                                                                                                                                              |
| Search scope                                                     | Case-insensitive substring match on title **and** description only                                                                                                                                   | Fake Store has no search endpoint, so matching happens server-side over the full product list (also satisfies "client never calls Fake Store directly," since search literally can't be proxied). Title+description is what a shopper searching for "backpack" or "waterproof" would expect; price/category text search wasn't requested.                                               |
| Enquiry form fields                                              | name, email (required), phone (optional), message (required), plus a hidden `product_id`                                                                                                             | Matches the criterion ("complete the subsequent form with my contact details") without inventing fields nobody asked for.                                                                                                                                                                                                                                                               |
| Category strings with spaces/punctuation (e.g. "men's clothing") | URL-encoded in links, matched case-insensitively and trimmed server-side                                                                                                                             | Fake Store's real category names contain spaces and apostrophes; this keeps category links working without a separate slug/category mapping table.                                                                                                                                                                                                                                      |
| Local email provider                                             | `MAIL_MAILER=log` by default — enquiry emails land in `storage/logs/laravel.log`, not a real inbox                                                                                                   | Satisfies "no paid services or accounts the reviewer would need to create" while still being inspectable.                                                                                                                                                                                                                                                                               |

## Requirements dropped or simplified, and why

- **No `EnquiryNotifier` interface.** Laravel's `Mail` facade + a `Mailable`
  class is already the swappable abstraction the brief asks for — changing
  providers is a `MAIL_MAILER` env change, not a code change. A hand-rolled
  wrapper interface around `Mail::send()` would add a file with no behavioral
  difference. Skipped as ceremony, not as a missing requirement.
- **No separate repository methods for categories/search/featured.** The
  `ProductProvider` contract is just `all()` and `find()`. The entire Fake
  Store catalogue is ~20 products, so category filtering, search, and
  featured selection are all cheap operations over one cached in-memory
  list — mirroring Fake Store's separate `/products/category/:cat` and
  `/products/categories` endpoints as provider methods would be unused
  surface area for a 20-row dataset.
- **No admin UI for curating featured products or managing categories.**
  Out of scope for a PoC; `FEATURED_PRODUCT_IDS` is an env var the owner
  (or a future admin panel) can change.
- **No queueing for enquiry emails.** They send synchronously. Fine at PoC
  traffic; see "what's next" below.
- **No rate-limiting library** — just Laravel's built-in `throttle`
  middleware (5/minute) on the enquiry endpoint, not a custom limiter.
- **No Docker.** `composer run dev` (one command, no containers) hits the
  "<10 minutes, 1-2 commands" bar more reliably than `docker compose up`
  would for a reviewer who may not have Docker Desktop running, and this
  stack needs PHP/Composer/Node locally either way to read a Laravel+React
  repo. The AWS-compatibility constraint is about _deployable_ infra choices,
  which this doesn't foreclose.
- **No client-side Zod schema for the enquiry form.** The server
  `StoreEnquiryRequest` is the single source of truth for validation rules;
  a parallel client-side schema would just be a second place for those rules
  to drift out of sync. Basic HTML5 `required`/`type=email` give immediate
  feedback; the server is the real boundary.

## How this grows beyond the PoC

- **Product data source**: write a new `ProductProvider` implementation
  (e.g. backed by a real database, or a different supplier API) and flip
  `PRODUCT_PROVIDER` in `.env`. No controller, route, or frontend code
  changes, because they only depend on the interface.
- **Look and feel**: all UI lives behind `CatalogueLayout` and a handful of
  shared components (`ProductCard`, `ProductGrid`, `EnquiryForm`). A
  redesign — including adopting a component library or design system —
  touches that one layer.
- **Email provider**: change `MAIL_MAILER` (SMTP, SES, Postmark, etc.) —
  the `EnquirySubmitted` Mailable and its Blade view don't change.
- **Scale**: the product list is cached for 10 minutes behind one cache key;
  raising the TTL, warming it on a schedule, or moving to a real database
  are all incremental changes, not rewrites.

## What we'd do with another day

- Queue enquiry emails instead of sending synchronously, with retry/backoff.
- Pagination for large categories/search results (fine at ~20 products now).
- An admin UI for picking featured products and managing categories, backed
  by a real database instead of Fake Store.
- Loading/error states that degrade gracefully in the UI when the product
  provider is unavailable, rather than a generic 503 page.
- End-to-end browser tests (Playwright) covering the acceptance criteria
  through a real browser, as a complement to the Pest feature tests — the
  AI building this had no Chrome available locally to do this itself (see
  `AI_USAGE.md`).
- A proper design pass once the entrepreneur's creative work is ready, since
  the current UI is intentionally plain Tailwind to keep focus on the
  acceptance criteria.
