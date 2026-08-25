# Project: Laravel 11 Restaurant Platform (Admin CRUDs & Frontend UI)

## Architecture
- **Framework**: Laravel 11 with SQLite in-memory for testing and MySQL for production.
- **Frontend Stack**: Tailwind CSS (Breeze) + Alpine.js for interactive modals/cart toggles.
- **Data Flow**:
  - Store Settings & Navigation stored in DB (`settings`, `navigation_menus`, `navigation_items`), loaded dynamically into frontend layouts via `AppServiceProvider` View Composer.
  - Catalog Management: Categories → Products → Product Variants.
  - Order Management: Session Cart (`session('cart')`) → Checkout Form → Atomic DB Transaction (`orders`, `order_items`) → Admin Order Management.
  - Zero-Terminal: Administration and configuration managed dynamically via UI and `SystemCommandController`.

## Feature Inventory
| # | Feature | Description | Milestone | Source |
|---|---------|-------------|-----------|--------|
| 1 | Domain Models & Relations | Eloquent models with $fillable, casts, and relations | M1 | Survey (DONE) |
| 2 | Factories & Seeders | Model factories and enriched DatabaseSeeder | M1 | Survey (DONE) |
| 3 | Admin Dashboard | Stats overview (orders, sales, products, pending) | M1 | R1 (DONE) |
| 4 | Admin Settings CRUD | Dynamic site identity, contact, hours, currency, tax | M1 | R1 (DONE) |
| 5 | Admin Pages CRUD | Dynamic CMS pages (About, Contact, Terms, etc.) | M1 | R1 (DONE) |
| 6 | Admin Navigation CRUD | Header & Footer menu manager and link ordering | M1 | R1 (DONE) |
| 7 | Admin Categories CRUD | Category management with sort order and image support | M1 | R1 (DONE) |
| 8 | Admin Products & Variants CRUD | Products with base price, category, and dynamic variants | M1 | R1 (DONE) |
| 9 | Admin Orders Management | Order listing, filtering, order & payment status updates | M1 | R1 (DONE) |
| 10 | Frontend Dynamic Layout | Header & Footer dynamically driven by DB settings & menus | M2 | R2 (DONE) |
| 11 | Frontend Homepage | Dynamic Hero, featured categories & popular dishes | M2 | R2 (DONE) |
| 12 | Frontend Menu Browsing | Category filter pills, product cards, Alpine variant modal | M2 | R2 (DONE) |
| 13 | Session Shopping Cart | Add, update quantity, remove, subtotal/tax calculations | M2 | R2 (DONE) |
| 14 | Customer Checkout Flow | Customer details, pickup/delivery, atomic DB transaction | M2 | R2 (DONE) |
| 15 | Order Confirmation & Page View | Dynamic order receipt and dynamic CMS page viewer | M2 | R2 (DONE) |
| 16 | Zero-Terminal System Tools | Whitelisted Artisan commands via SystemCommandController | M1/M2 | R3 (DONE) |
| 17 | E2E & Adversarial Testing | Comprehensive 5-Tier PHPUnit test suite & audit (183 tests) | M3 | Acceptance (DONE) |

## Code Layout
- `app/Models/`: `Setting.php`, `Page.php`, `NavigationMenu.php`, `NavigationItem.php`, `Category.php`, `Product.php`, `ProductVariant.php`, `Order.php`, `OrderItem.php`, `User.php`
- `app/Http/Controllers/Admin/`: `DashboardController.php`, `SettingController.php`, `PageController.php`, `NavigationController.php`, `CategoryController.php`, `ProductController.php`, `OrderController.php`, `SystemCommandController.php`
- `app/Http/Controllers/`: `HomeController.php`, `MenuController.php`, `CartController.php`, `CheckoutController.php`, `PageController.php`
- `app/Providers/`: `AppServiceProvider.php` (Global View Composer)
- `resources/views/layouts/`: `app.blade.php`, `navigation.blade.php`, `frontend.blade.php`
- `resources/views/admin/`: `dashboard.blade.php`, `settings/`, `pages/`, `navigation/`, `categories/`, `products/`, `orders/`, `system.blade.php`
- `resources/views/frontend/`: `home.blade.php`, `menu.blade.php`, `cart.blade.php`, `checkout.blade.php`, `order-confirmation.blade.php`, `page.blade.php`
- `database/factories/`: Domain model factories for all 10 entities
- `database/seeders/`: `DatabaseSeeder.php`
- `tests/Feature/Admin/`: Feature tests for all Admin CRUDs
- `tests/Feature/Frontend/`: Feature tests for Homepage, Menu, Cart, Checkout, Pages
- `tests/Feature/E2E/`: 4-Tier Opaque-box E2E tests (100 tests)
- `tests/Feature/Adversarial/`: Adversarial stress and dynamic reconfiguration tests (31 tests)

## Milestones
| # | Name | Scope | Dependencies | Status |
|---|------|-------|-------------|--------|
| E2E | E2E Testing Track | Test Infrastructure, Tiers 1-4 Test Suites, TEST_READY.md | none | DONE |
| M1 | Admin Panel CRUDs & Core Models | Models, Factories, Seeders, Admin Controllers, Blade views, Admin tests | none | DONE |
| M2 | Frontend UI, Menu & Cart Flow | Frontend Layout, Homepage, Menu, Session Cart, Checkout, Frontend tests | M1 | DONE |
| M3 | Final Verification & Hardening | 100% E2E test pass (183 tests), Adversarial test suite, Forensic audit | M1, M2, E2E | DONE |

## Interface Contracts
### Setting Model & Helper
- `Setting::get(string $key, mixed $default = null): mixed`
- `Setting::set(string $key, mixed $value, string $type = 'string'): void`

### Session Cart Contract (`session('cart')`)
- Data structure:
  ```php
  [
      'item_key' => [
          'product_id' => int,
          'product_name' => string,
          'variant_id' => ?int,
          'variant_name' => ?string,
          'price' => float,
          'quantity' => int,
          'image' => ?string,
          'subtotal' => float,
      ]
  ]
  ```
- Subtotal: sum of items (`price * quantity`).
- Tax: calculated dynamically based on `Setting::get('tax_rate', 0)`.
- Total: `subtotal + tax + delivery_fee`.

### Checkout Submission Contract (`POST /checkout`)
- Input: `customer_name`, `customer_email`, `customer_phone`, `order_type` (pickup/delivery), `delivery_address` (nullable/required for delivery), `order_notes` (nullable), `payment_method` (cod/stripe/square/cash).
- Output: Creates `Order` with `order_number` / ID, inserts `OrderItem` records with selected variant JSON, clears `session('cart')`, redirects to `checkout.success` with order details.
