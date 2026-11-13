# Interview Prep Notes

Personal study notes for defending this submission in the follow-up
interview. Not one of the three required deliverables (`README.md`,
`DECISIONS.md`, `AI_USAGE.md`) — this is a plain-language walkthrough of
the project plus the Q&A decisions made while building it.

## Decisions made during the build (and why)

| #   | Question                                                                                                                                                                                                                    | Decision                                                                        | Reasoning                                                                                                                                                                                                                                                                                |
| --- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1   | A prior memory claimed a finished implementation already existed in this folder, but it was actually empty.                                                                                                                 | Build fresh, from scratch.                                                      | Verified the directory was genuinely empty before writing any code — never trust a stale memory/claim over the actual filesystem.                                                                                                                                                        |
| 2   | `gh` CLI (needed for GitHub PRs) wasn't installed.                                                                                                                                                                          | Install it.                                                                     | Required for the submission's PR workflow; no reason not to.                                                                                                                                                                                                                             |
| 3   | A pasted "orchestration" doc wanted an elaborate multi-branch workflow (an `integration` branch, a dedicated branch + PR _per feature_, ~8 PRs total). The challenge brief itself just wants one PR in a 2-3 hour time-box. | Follow the challenge brief: one `feature/catalogue` branch, one PR into `main`. | The elaborate ceremony would burn hours on process instead of the product, directly contradicting the brief's own time-box and "focus on the critical path" guidance. The _content_ of those per-feature docs (edge cases, test ideas) was still used — just not the branching ceremony. |
| 4   | Public or private GitHub repo?                                                                                                                                                                                              | Public.                                                                         | Simplest option; no need to invite specific reviewer accounts.                                                                                                                                                                                                                           |
| 5   | More of the same pasted doc arrived mid-build, again asking for per-feature branches into `integration`.                                                                                                                    | Re-confirmed: stay on the single-branch plan.                                   | Consistency — the first answer to this exact question doesn't change because the same document got pasted again in pieces.                                                                                                                                                               |
| 6   | GitHub auth needed interactive login, which an AI agent can't complete itself.                                                                                                                                              | User ran `gh auth login` themselves.                                            | OAuth/browser login requires a human in the loop; this is a hard boundary, not a preference.                                                                                                                                                                                             |

## What the project actually is

A product catalogue proof-of-concept: browse by category, search, view
product details, and submit an enquiry that emails a sales inbox. The
brief was explicit that this isn't a throwaway prototype — the product
data source, the look and feel, and the email provider are all expected
to change later, so the architecture needs clean seams at exactly those
three points.

**Stack:** Laravel (PHP) backend, React + TypeScript frontend, glued
together by **Inertia.js**. SQLite for local dev, Pest for tests.

**Why Inertia instead of a separate API + SPA?** A traditional
React-frontend/API-backend split means two deployable things, CORS to
configure, and often duplicated validation/auth logic between them.
Inertia lets Laravel stay the single source of truth — controllers return
"render this React page with this data" instead of raw JSON, and Inertia
handles turning that into a real page navigation in the browser. One app,
one deploy.

## Request flow, end to end

1. Browser requests `/` → `HomeController::index()`.
2. Controller asks the bound `ProductProvider` for `all()` products, runs
   `FeaturedProductSelector::select()` to pick 5, and calls
   `Inertia::render('home', [...])`.
3. Inertia sends HTML + a JSON payload describing "render the `home`
   page with these props"; React takes over from there.
4. `resources/js/pages/home.tsx` receives `featured: Product[]` as props
   and renders a `ProductGrid` of `ProductCard`s.
5. Category (`CategoryController`), search (`SearchController`), and
   product detail (`ProductController`) follow the identical pattern —
   different controller, same "ask the provider → shape the data → render
   a page" shape.
6. Enquiry is the one write path: `EnquiryController::store()` validates,
   looks up the product, sends mail, returns `back()`. The frontend's
   `onSuccess` callback (not a server redirect to a different page) is
   what shows the "thank you" message.

**The client never talks to Fake Store directly** — every product request
goes through Laravel first. That was a hard constraint in the brief
(#2 of the technical constraints), and it falls out naturally from the
architecture rather than needing special-casing.

## Why `ProductProvider` exists (the most important design decision)

The brief says the product data source "is expected to change." If every
controller called Fake Store's HTTP API directly, swapping it out later
means hunting down and rewriting every controller.

The fix: **depend on an interface, not a concrete implementation.**

- `App\Catalogue\Contracts\ProductProvider` — an interface, just a promise:
  "whatever implements me has an `all()` and a `find($id)`." No logic.
- `App\Catalogue\FakeStoreProductProvider` — the one class that actually
  implements that promise today, by calling Fake Store's real API.
- Every controller asks Laravel's container for "something that is a
  `ProductProvider`" — it has no idea it's Fake Store underneath.
- One binding, in `AppServiceProvider::register()`, tells Laravel which
  concrete class to hand out when that interface is requested:
    ```php
    $this->app->bind(ProductProvider::class, match (config('catalogue.provider')) {
        'fake_store' => FakeStoreProductProvider::class,
        default => FakeStoreProductProvider::class,
    });
    ```

To swap the data source later: write one new class implementing the same
two methods, flip the `PRODUCT_PROVIDER` env var (or add a new `match`
arm), done. No controller, no route, no React page changes.

**Why the interface only has two methods** (`all()`, `find()`) and not
one per Fake Store endpoint (category, search, etc.): the whole catalogue
is ~20 products. Category filtering and search are just PHP array
filtering over what `all()` already returns. Adding more interface surface
would be solving a scaling problem that doesn't exist yet.

**Why there's no equivalent interface for email** (no
`EnquiryNotifier`): Laravel's own `Mail` facade + `MAIL_MAILER` env var
_is already_ the swappable abstraction the brief is asking for — changing
providers is a config change, not a code change. A hand-rolled wrapper
around `Mail::send()` would just rename it with zero added benefit.

## Traits used, and what they actually do

A PHP **trait** is `use SomeTrait;` inside a class — a way to pull in a
reusable chunk of behavior without inheritance. Two traits appear in this
codebase, both standard Laravel building blocks, not custom-written:

1. **`Queueable`** and **`SerializesModels`** — in
   `app/Mail/EnquirySubmitted.php`:

    ```php
    class EnquirySubmitted extends Mailable
    {
        use Queueable, SerializesModels;
    ```
    - `Queueable` gives the Mailable the _option_ to be sent in the
      background later via `->queue()` instead of `->send()`. Not used yet
      (mail sends synchronously, fine at this scale) — but it's the
      standard shape every Laravel Mailable is built from, so there's no
      cost to including it, and the door stays open for "what's next" #1
      in `DECISIONS.md` (queueing enquiry emails).
    - `SerializesModels` makes sure that _if_ this were ever queued, any
      Eloquent models attached get stored as just an ID and re-fetched
      fresh when a queue worker processes it, instead of serializing a
      potentially huge object graph into the queue.

2. **`RefreshDatabase`** — in `tests/Pest.php`:
    ```php
    pest()->extend(TestCase::class)->use(RefreshDatabase::class)->in('Feature');
    ```
    Resets the test database to a clean slate before every test runs, so
    one test's leftover data can never leak into and break another.

## The test suite

- Every test binds `tests/Fakes/FakeProductProvider.php` over the real
  `ProductProvider` interface — 8 hardcoded products, zero network calls.
  This is the direct payoff of the interface: production asks for
  `ProductProvider` and gets Fake Store, tests ask for the same interface
  and get a predictable fake. Fast, no flakiness, known expected values.
- **One test file per acceptance criterion** — a direct line from "the
  client's requirement" to "the test that proves it":
  `HomePageTest`, `CategoryPageTest`, `SearchTest`, `ProductDetailTest`,
  `EnquiryTest`.
- `ProductProviderCachingTest.php` is the odd one out — a regression test
  for a real bug (see below), deliberately forcing the real `database`
  cache driver instead of the simplified one the rest of the suite uses.
- Inertia responses are asserted with `assertInertia()`, which inspects
  the actual props sent to React (e.g. `->has('featured', 5)`) without
  needing a browser.

## The frontend

- **Pages** (`resources/js/pages/*.tsx`) — one per route, each receiving
  exactly the props its controller sent.
- **Components** (`ProductCard`, `ProductGrid`, `EnquiryForm`) — reusable
  pieces shared across Home/Category/Search so the same grid-rendering
  logic isn't duplicated three times.
- **Layout** (`CatalogueLayout.tsx`) — shared header/nav/search bar every
  page wraps itself in.
- Category nav links get their data "for free" via a globally shared
  Inertia prop (`HandleInertiaRequests::share()`), not a per-page fetch.
- `EnquiryForm` toggles between the form and a "thank you" message using
  local React state — no separate success page/route needed. Inertia's
  `useForm` tracks validation errors and exposes `processing`, which
  disables the submit button to prevent double-submission.

## The enquiry/email flow

1. Submit → `StoreEnquiryRequest` validates server-side _before_ the
   controller runs (name/message required, real email format, max
   lengths).
2. Controller looks up the product by the submitted `product_id` — this
   matters because that field is client-controlled and could be tampered
   with; an unknown id returns 404.
3. Builds an `EnquirySubmitted` Mailable, sends it to `SALES_INBOX_EMAIL`.
4. **"Don't claim success if sending failed"** (an explicit brief
   requirement): the send is wrapped in try/catch. A failure doesn't
   redirect back as if it worked — it throws a validation error the user
   actually sees, instead of a false "thank you."
5. Locally, `MAIL_MAILER=log` means the email is written straight into
   `storage/logs/laravel.log` — provable, inspectable, no real email
   account needed ("no paid services" constraint).
6. CSRF protection and duplicate-submission prevention aren't custom
   code — they're Inertia/Laravel defaults (CSRF token auto-attached,
   `processing` flag from `useForm`).

## A real bug this project caught (good interview material)

`FakeStoreProductProvider::all()` originally cached hydrated `Product`
objects via `Cache::remember()`. Every automated check was green — 16
Pest tests, PHPStan, a clean build. But the **second** page load against
the live app threw:

```
FakeStoreProductProvider::all(): Return value must be of type
Illuminate\Support\Collection, __PHP_Incomplete_Class returned
```

**Root cause:** Laravel 13 ships a security default
(`cache.serializable_classes => false`) that makes the database cache
driver refuse to unserialize arbitrary PHP objects — it silently converts
them to a useless stub instead of throwing an obvious error. The test
suite never caught this because tests use a simplified in-memory cache
driver that never serializes anything at all, so the bug only existed on
the "real" cache path.

**Fix:** cache the raw array payload from the API instead of hydrated
objects, and rebuild `Product` objects from that array on every read
(cheap for a 20-item list). Added `ProductProviderCachingTest.php`, which
deliberately forces the real database cache driver, specifically so this
class of bug can't silently reappear.

**Why this is worth bringing up unprompted:** it's a concrete example of
"all tests green" not meaning "the app actually works" — it was only
caught by running the real app and reading Laravel's own cache source
code to find the true root cause, not by guessing at a fix.

## Likely follow-up questions to have answers ready for

- _"What if Fake Store's API is down?"_ — `FakeStoreProductProvider`
  catches the HTTP failure/timeout and throws a 503 (`HttpException`),
  which Laravel turns into a generic error page. Not a graceful
  degraded UI — flagged as a "what's next" item in `DECISIONS.md`.
- _"Why SQLite instead of a real database?"_ — This app doesn't actually
  store product data in a database at all (Fake Store is the source of
  truth); SQLite only backs Laravel's own framework tables (sessions,
  cache, jobs). Zero setup for a reviewer, which matters for the
  "<10 minutes" constraint.
- _"How would you add a real database-backed product source?"_ — New
  class implementing `ProductProvider`, reading from Eloquent models
  instead of HTTP; flip the `PRODUCT_PROVIDER` config; nothing else
  changes.
- _"Why didn't you use Zod on the frontend for the enquiry form?"_ —
  The server `FormRequest` is the single source of validation truth;
  a duplicate client-side schema would just be a second place for rules
  to drift out of sync. Basic HTML5 attributes (`required`, `type=email`)
  give immediate feedback without that duplication.
