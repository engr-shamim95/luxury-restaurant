## 2026-08-24T22:02:43Z
You are the Implementation Worker for Milestone 2: Frontend Customer UI, Dynamic Layout, Menu Browsing, Session-based Cart & Checkout Flow.

Working Directory: i:\Client Restaurant\.agents\m2_worker_1\
Original Request: i:\Client Restaurant\.agents\ORIGINAL_REQUEST.md
Project Blueprint: i:\Client Restaurant\PROJECT.md
E2E Test Inventory: i:\Client Restaurant\TEST_READY.md

MANDATORY INTEGRITY WARNING:
DO NOT CHEAT. All implementations must be genuine. DO NOT hardcode test results, create dummy/facade implementations, or circumvent the intended task. A teamwork_preview_auditor will independently verify your work. Integrity violations WILL be detected and your work WILL be rejected.

Your Task:
1. Read `i:\Client Restaurant\.agents\ORIGINAL_REQUEST.md`, `i:\Client Restaurant\PROJECT.md`, `i:\Client Restaurant\TEST_READY.md`, and `i:\Client Restaurant\.agents\explorer_survey_frontend\analysis.md`.
2. Implement Frontend Layout (`resources/views/layouts/frontend.blade.php`):
   - Dynamic Header: Store name / logo from `Setting::get('site_name')`, `Setting::get('site_logo')`, dynamic header navigation items from `NavigationMenu::getByLocation('header')`, and dynamic cart badge counter showing total quantity in `session('cart')`.
   - Dynamic Footer: About/tagline from `Setting::get('site_tagline')` / `site_description`, contact info (`phone`, `email`, `address`), opening hours (`opening_hours`), social links (`facebook_url`, `instagram_url`, `twitter_url`), copyright text (`copyright_text`), dynamic footer navigation items from `NavigationMenu::getByLocation('footer')`.
   - ZERO hardcoded restaurant names, phone numbers, prices, or links in the layout or any frontend view.
3. Implement Customer-Facing Controllers in `app/Http/Controllers/`:
   - `HomeController.php`: `index()` renders `frontend.home` with hero banner (dynamic title/subtitle/CTA from settings), featured categories (`Category::active()->ordered()->take(...)`), featured products (`Product::available()->with(['category', 'variants'])->take(...)`), and restaurant info.
   - `MenuController.php`: `index(Request $request)` renders `frontend.menu` with all active categories, available products (filtered by category if `?category=slug` is provided, or all categorized), eager loading variants (`ProductVariant::active()`).
   - `CartController.php`:
     * `index()`: renders `frontend.cart` with items in `session('cart', [])`, subtotal calculation, dynamic tax calculation based on `Setting::get('tax_rate', 0)`, delivery fee from `Setting::get('delivery_fee', 0)`, total calculation.
     * `add(Request $request)`: validates `product_id`, optional `variant_id`, and `quantity` (default 1). Adds to `session('cart')` with unique item key (`$productId` or `{$productId}_{$variantId}`), stores product name, price (base price + variant adjustment), variant name, quantity, image, and total. Returns redirect or json with success message.
     * `update(Request $request)`: updates quantity for a given item key, removing if quantity <= 0.
     * `remove(Request $request)`: removes item key from session cart.
     * `clear()`: purges session cart.
   - `CheckoutController.php`:
     * `index()`: redirects to `/cart` if cart is empty. Renders `frontend.checkout` with cart items, totals, delivery options, and customer input fields.
     * `store(Request $request)`: validates `customer_name`, `customer_email`, `customer_phone`, `order_type` (in:pickup,delivery), `delivery_address` (required if order_type is delivery), `order_notes` (nullable), `payment_method` (in:cod,stripe,cash). Executes atomic `DB::transaction()`: creates `Order` with calculated subtotal, tax, delivery fee, total, pending status; creates `OrderItem` records for each cart item with selected variants JSON; clears `session('cart')`; redirects to `checkout.success` with order reference.
     * `success(Order $order)`: renders `frontend.order-confirmation` with order summary, customer info, delivery details, status badge, and receipt view.
   - `PageController.php`: `show(string $slug)` renders `frontend.page` for published CMS page (`Page::where('slug', $slug)->published()->firstOrFail()`), setting dynamic SEO meta tags.
4. Implement Customer-Facing Blade Views in `resources/views/frontend/`:
   - `home.blade.php`: Hero section, featured menu categories, popular dishes cards with price & "Add to Order" action, restaurant story / about snippet, call to action.
   - `menu.blade.php`: Category filter navigation pills, search bar, product cards with badge/pricing/image/description, and Alpine.js variant selection modal for products with variants (`has_variants = true`).
   - `cart.blade.php`: Clean Tailwind table / card layout of cart items, quantity spinners (+ / -), remove buttons, order summary card (subtotal, tax, delivery fee, total), promo/clear button, and "Proceed to Checkout" button.
   - `checkout.blade.php`: Two-column layout with Customer Details form, Delivery vs Pickup toggle (shows/hides delivery address with Alpine.js), Payment method radio selector (Cash on Delivery / Card), Order Summary breakdown with tax, and "Place Order" button.
   - `order-confirmation.blade.php`: Thank you banner, Order Number (`#ORD-XXXXX`), status pill, pickup/delivery instructions, estimated preparation time, itemized table with variant details, totals, and "Back to Menu" link.
   - `page.blade.php`: Clean CMS page content renderer with page title, meta description, and formatted body content.
5. Register frontend routes in `routes/web.php`:
   - `GET /` -> `HomeController@index` (`name('home')`)
   - `GET /menu` -> `MenuController@index` (`name('menu')`)
   - `GET /cart` -> `CartController@index` (`name('cart.index')`)
   - `POST /cart/add` -> `CartController@add` (`name('cart.add')`)
   - `PATCH /cart/update` -> `CartController@update` (`name('cart.update')`)
   - `DELETE /cart/remove` -> `CartController@remove` (`name('cart.remove')`)
   - `POST /cart/clear` -> `CartController@clear` (`name('cart.clear')`)
   - `GET /checkout` -> `CheckoutController@index` (`name('checkout.index')`)
   - `POST /checkout` -> `CheckoutController@store` (`name('checkout.store')`)
   - `GET /checkout/success/{order}` -> `CheckoutController@success` (`name('checkout.success')`)
   - `GET /page/{slug}` -> `PageController@show` (`name('page.show')`)
6. Run PHPUnit test suites:
   - Run `php artisan test tests/Feature/E2E`
   - Run `php artisan test tests/Feature/Admin`
   - Run the full test suite `php artisan test`
   - Verify that all 100+ tests across all tiers pass with 0 errors and 0 failures.
7. Write your handoff report to `i:\Client Restaurant\.agents\m2_worker_1\handoff.md` and send a message to parent when complete.
