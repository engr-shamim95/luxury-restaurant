# Survey Report: Testing Framework, Database & System Configuration

**Explorer Agent**: `explorer_survey_testing`  
**Date**: 2026-08-25  
**Workspace**: `i:\Client Restaurant`  
**Target Milestone**: Comprehensive System & Test Survey

---

## 1. Executive Summary

This report documents the architectural survey of the **Testing Framework**, **Database Configuration**, and **Zero-Terminal System Command Utilities** for the Laravel 11/13 restaurant platform.

Key findings:
1. **Testing Framework**: Standard **PHPUnit 12.5.12** is configured. Pest is not installed. Automated testing leverages Laravel's `TestCase` with `RefreshDatabase` against an isolated SQLite in-memory database (`:memory:`).
2. **Database Configuration**: Production/local environment uses MySQL (`restaurant_db`) or file-based SQLite (`database.sqlite`), whereas test execution in `phpunit.xml` forces `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`, `SESSION_DRIVER=array`, and `QUEUE_CONNECTION=sync`.
3. **Database Schema & Migrations**: All 10 domain and system migrations are already created and structurally complete (`users`, `settings`, `pages`, `navigation_menus`, `navigation_items`, `categories`, `products`, `product_variants`, `orders`, `order_items`). However, the Eloquent models (`app/Models/*.php`) are empty skeleton classes needing `$fillable` attributes and relationship methods.
4. **Test Factories & Coverage**: Only `UserFactory.php` exists. Domain factories (`CategoryFactory`, `ProductFactory`, `ProductVariantFactory`, `SettingFactory`, `PageFactory`, `NavigationMenuFactory`, `NavigationItemFactory`, `OrderFactory`, `OrderItemFactory`) are missing and must be created to support comprehensive automated tests.
5. **Zero-Terminal Constraint (R3)**: `SystemCommandController` (`app/Http/Controllers/Admin/SystemCommandController.php`) and its corresponding Blade view (`resources/views/admin/system.blade.php`) provide a secure, authenticated web interface (`['auth', 'admin']`) to run whitelisted Artisan commands (`cache:clear`, `config:clear`, `route:clear`, `view:clear`, `optimize:clear`, `storage:link`) via `Artisan::call()`.

---

## 2. Testing Framework Architecture

### 2.1 Configuration (`phpunit.xml`)
The PHPUnit configuration is defined in `phpunit.xml`:
- **Schema**: `vendor/phpunit/phpunit/phpunit.xsd` (PHPUnit 12 compatible)
- **Bootstrap**: `vendor/autoload.php`
- **Suites**:
  - `Unit`: Points to `tests/Unit`
  - `Feature`: Points to `tests/Feature`
- **Code Coverage Inclusion**: `app/` directory
- **Test Environment Variables**:
  ```xml
  <env name="APP_ENV" value="testing"/>
  <env name="APP_MAINTENANCE_DRIVER" value="file"/>
  <env name="BCRYPT_ROUNDS" value="4"/>
  <env name="BROADCAST_CONNECTION" value="null"/>
  <env name="CACHE_STORE" value="array"/>
  <env name="DB_CONNECTION" value="sqlite"/>
  <env name="DB_DATABASE" value=":memory:"/>
  <env name="DB_URL" value=""/>
  <env name="MAIL_MAILER" value="array"/>
  <env name="QUEUE_CONNECTION" value="sync"/>
  <env name="SESSION_DRIVER" value="array"/>
  <env name="PULSE_ENABLED" value="false"/>
  <env name="TELESCOPE_ENABLED" value="false"/>
  <env name="NIGHTWATCH_ENABLED" value="false"/>
  ```

### 2.2 Base TestCase & Execution
- **Base Class**: `tests/TestCase.php` extends `Illuminate\Foundation\Testing\TestCase as BaseTestCase`.
- **Database Reset**: Feature tests use `use Illuminate\Foundation\Testing\RefreshDatabase;`. Under SQLite `:memory:`, this runs all migrations upon test suite initialization and wraps each test in a database transaction or resets SQLite in-memory state rapidly.
- **Session Handling**: `SESSION_DRIVER=array` enables testing session persistence within single requests and across multi-step test sequences (`$this->withSession(...)`, `$this->session(...)`, `$this->get(...)`, `$this->post(...)`).
- **Composer Test Script**: `composer.json` defines `"test": ["@php artisan config:clear --ansi @no_additional_args", "@php artisan test"]`. Tests can also be executed directly via `php artisan test` or `vendor/bin/phpunit`.

### 2.3 Existing Test Suite Inventory
Located in `tests/`:
- `tests/Feature/Auth/AuthenticationTest.php`: Login screen rendering, valid credentials, invalid password, logout.
- `tests/Feature/Auth/EmailVerificationTest.php`: Email verification flow and notifications.
- `tests/Feature/Auth/PasswordConfirmationTest.php`: Password confirmation screen and verification.
- `tests/Feature/Auth/PasswordResetTest.php`: Reset password link request and reset handling.
- `tests/Feature/Auth/PasswordUpdateTest.php`: Updating password from profile.
- `tests/Feature/Auth/RegistrationTest.php`: User registration and automatic authentication.
- `tests/Feature/ProfileTest.php`: Profile view, update profile information, and account deletion.
- `tests/Feature/ExampleTest.php`: Root `/` status 200 check.
- `tests/Unit/ExampleTest.php`: Basic true assertion.

*Missing Coverage (Required by Acceptance Criteria)*:
- Admin CRUD controllers (`Settings`, `Pages`, `Navigation`, `Categories`, `Products`, `Product Variants`, `Orders`).
- Frontend customer routes (Homepage, Menu browsing, dynamic settings from DB).
- Session-based Shopping Cart actions (Add to cart, update quantity, remove item, cart view).
- Checkout submission flow.

---

## 3. Database Configuration & Schema Analysis

### 3.1 Connection Environments
- **Local/Development (`.env`)**:
  - `DB_CONNECTION=mysql`
  - `DB_HOST=127.0.0.1`
  - `DB_PORT=3306`
  - `DB_DATABASE=restaurant_db`
  - `DB_USERNAME=root`
  - `DB_PASSWORD=`
- **Fallback / Example (`.env.example`)**:
  - `DB_CONNECTION=sqlite`
- **Testing (`phpunit.xml`)**:
  - `DB_CONNECTION=sqlite`
  - `DB_DATABASE=:memory:`
  - SQLite foreign key constraints are enabled by default in `config/database.php` (`'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true)`).

### 3.2 Schema & Migrations Inventory
All migration files are located in `database/migrations/`:

| Migration File | Table | Key Fields & Constraints | Relationships |
|---|---|---|---|
| `0001_01_01_000000_create_users_table.php` | `users` | `id`, `name`, `email` (unique), `password`, `is_admin` (bool, default `false`), `remember_token`, timestamps | Parent of `orders` (`user_id` nullable) |
| `2026_08_25_040844_create_settings_table.php` | `settings` | `id`, `key` (unique string), `value` (nullable text), `type` (default `'string'`), timestamps | Standalone key-value store |
| `2026_08_25_040845_create_pages_table.php` | `pages` | `id`, `title`, `slug` (unique), `content` (longText nullable), `meta_title`, `meta_description`, `og_image`, `is_published` (bool default `true`), timestamps | Target of `navigation_items.page_id` |
| `2026_08_25_040846_create_navigation_menus_table.php` | `navigation_menus` | `id`, `name`, `location` (unique string, e.g. `'header'`, `'footer'`), timestamps | Has many `navigation_items` |
| `2026_08_25_040847_create_navigation_items_table.php` | `navigation_items` | `id`, `navigation_menu_id` (foreign key cascade), `label`, `url` (nullable), `page_id` (foreign key set null), `order` (int default 0), `target` (default `'_self'`), timestamps | Belongs to `navigation_menus`, optional `pages` |
| `2026_08_25_040848_create_categories_table.php` | `categories` | `id`, `name`, `slug` (unique), `image` (nullable), `description` (nullable), `is_active` (bool default `true`), `order` (int default 0), timestamps | Has many `products` |
| `2026_08_25_040849_create_products_table.php` | `products` | `id`, `category_id` (foreign key cascade), `name`, `slug` (unique), `description` (nullable), `base_price` (decimal 10,2), `image` (nullable), `is_available` (bool default `true`), `has_variants` (bool default `false`), timestamps | Belongs to `categories`, has many `product_variants`, referenced by `order_items` |
| `2026_08_25_040850_create_product_variants_table.php` | `product_variants` | `id`, `product_id` (foreign key cascade), `name`, `type` (default `'size'`), `price_adjustment` (decimal 10,2 default 0), `is_active` (bool default `true`), timestamps | Belongs to `products` |
| `2026_08_25_040851_create_orders_table.php` | `orders` | `id`, `user_id` (foreign key set null), `customer_name`, `customer_email`, `customer_phone` (nullable), `order_type` (default `'pickup'`), `delivery_address` (nullable), `order_notes` (nullable), `subtotal` (decimal 10,2), `tax` (decimal 10,2 default 0), `total` (decimal 10,2), `payment_method`, `payment_status` (default `'pending'`), `order_status` (default `'new'`), `transaction_id` (nullable), timestamps | Has many `order_items`, optional `users` |
| `2026_08_25_040852_create_order_items_table.php` | `order_items` | `id`, `order_id` (foreign key cascade), `product_id` (foreign key set null), `product_name`, `quantity` (int), `unit_price` (decimal 10,2), `variants_selected` (json nullable), `total_price` (decimal 10,2), timestamps | Belongs to `orders`, optional `products` |

### 3.3 Eloquent Model Inspection & Required Updates
Inspection of `app/Models/*.php` shows that all domain models are empty skeletons. To support tests and CRUD operations without mass assignment exceptions or broken relations, the following must be implemented:
1. `App\Models\User`: Add `is_admin` to casts (`'is_admin' => 'boolean'`) and helper method/state for admin check.
2. `App\Models\Setting`: Fillable `['key', 'value', 'type']`. Helper static methods `Setting::get($key, $default = null)` and `Setting::set($key, $value, $type = 'string')` recommended.
3. `App\Models\Page`: Fillable `['title', 'slug', 'content', 'meta_title', 'meta_description', 'og_image', 'is_published']`. Cast `is_published` as `boolean`.
4. `App\Models\NavigationMenu`: Fillable `['name', 'location']`. Relation `items()` -> `hasMany(NavigationItem::class)->orderBy('order')`.
5. `App\Models\NavigationItem`: Fillable `['navigation_menu_id', 'label', 'url', 'page_id', 'order', 'target']`. Relations `menu()` -> `belongsTo(NavigationMenu::class)` and `page()` -> `belongsTo(Page::class)`.
6. `App\Models\Category`: Fillable `['name', 'slug', 'image', 'description', 'is_active', 'order']`. Relation `products()` -> `hasMany(Product::class)`. Cast `is_active` as `boolean`.
7. `App\Models\Product`: Fillable `['category_id', 'name', 'slug', 'description', 'base_price', 'image', 'is_available', 'has_variants']`. Relations `category()` -> `belongsTo(Category::class)` and `variants()` -> `hasMany(ProductVariant::class)`. Casts `base_price` as `decimal:2`, `is_available` and `has_variants` as `boolean`.
8. `App\Models\ProductVariant`: Fillable `['product_id', 'name', 'type', 'price_adjustment', 'is_active']`. Relation `product()` -> `belongsTo(Product::class)`. Cast `price_adjustment` as `decimal:2`, `is_active` as `boolean`.
9. `App\Models\Order`: Fillable `['user_id', 'customer_name', 'customer_email', 'customer_phone', 'order_type', 'delivery_address', 'order_notes', 'subtotal', 'tax', 'total', 'payment_method', 'payment_status', 'order_status', 'transaction_id']`. Relations `items()` -> `hasMany(OrderItem::class)` and `user()` -> `belongsTo(User::class)`.
10. `App\Models\OrderItem`: Fillable `['order_id', 'product_id', 'product_name', 'quantity', 'unit_price', 'variants_selected', 'total_price']`. Cast `variants_selected` as `array` or `json`. Relation `order()` -> `belongsTo(Order::class)`.

---

## 4. Test Factories & Helpers Plan

### 4.1 Required Factories (`database/factories/`)
To enable tests to instantiate realistic data quickly, the following factories are needed:
1. `UserFactory`: Add `admin()` state:
   ```php
   public function admin(): static
   {
       return $this->state(fn (array $attributes) => [
           'is_admin' => true,
       ]);
   }
   ```
2. `CategoryFactory`: Generates `name`, unique `slug`, `description`, `is_active => true`, `order => 0`.
3. `ProductFactory`: Generates `category_id => Category::factory()`, `name`, `slug`, `description`, `base_price => 12.99`, `is_available => true`, `has_variants => false`.
4. `ProductVariantFactory`: Generates `product_id => Product::factory()`, `name => 'Large'`, `type => 'size'`, `price_adjustment => 3.50`, `is_active => true`.
5. `SettingFactory`: Generates `key`, `value`, `type => 'string'`.
6. `PageFactory`: Generates `title`, `slug`, `content`, `is_published => true`.
7. `NavigationMenuFactory`: Generates `name`, `location => 'header'`.
8. `NavigationItemFactory`: Generates `navigation_menu_id => NavigationMenu::factory()`, `label`, `url => '/'`, `order => 0`.
9. `OrderFactory`: Generates customer details, `subtotal => 25.00`, `tax => 2.50`, `total => 27.50`, `payment_method => 'cash'`, `payment_status => 'pending'`, `order_status => 'new'`.
10. `OrderItemFactory`: Generates `order_id => Order::factory()`, `product_name`, `quantity => 2`, `unit_price => 12.50`, `total_price => 25.00`.

### 4.2 Seeders Plan (`database/seeders/DatabaseSeeder.php`)
The default seeder should populate essential default database records:
- **Admin User**: Email `admin@restaurant.com`, password `password`, `is_admin = true`.
- **Core Settings**:
  - `restaurant_name`: "Bistro Bella"
  - `restaurant_phone`: "+1 (555) 123-4567"
  - `restaurant_email`: "contact@bistrobella.com"
  - `restaurant_address`: "123 Culinary Ave, Foodville, NY 10001"
  - `currency_symbol`: "$"
  - `tax_rate`: "10"
  - `hero_title`: "Authentic Artisan Cuisine"
  - `hero_subtitle`: "Freshly prepared daily with the finest locally sourced ingredients."
  - `opening_hours`: "Mon-Sun: 11:00 AM - 10:00 PM"
- **Navigation Menus**:
  - Location `header`: Items for "Home" (`/`), "Menu" (`/menu`), "About Us" (`/page/about`), "Contact" (`/page/contact`).
  - Location `footer`: Items for "Privacy Policy" (`/page/privacy`), "Terms of Service" (`/page/terms`).
- **Default Pages**:
  - "About Us" (`slug: about`)
  - "Contact" (`slug: contact`)
  - "Privacy Policy" (`slug: privacy`)
  - "Terms of Service" (`slug: terms`)
- **Categories & Products**:
  - Starters, Mains, Desserts, Beverages with sample items and variants.

---

## 5. Zero-Terminal Constraint (R3) & System Configuration

### 5.1 Requirement Analysis
Requirement R3 states: *"Do not introduce dependencies or architectural changes requiring end-user terminal commands. The existing `SystemCommandController` handles basic Artisan tasks."*

### 5.2 Implementation Breakdown
- **Controller**: `app/Http/Controllers/Admin/SystemCommandController.php`
  - `index()`: Returns `admin.system` view.
  - `runCommand(Request $request)`: Validates `$request->input('command')` against `$allowed` array:
    ```php
    $allowed = [
        'cache:clear',
        'config:clear',
        'route:clear',
        'view:clear',
        'optimize:clear',
        'storage:link'
    ];
    ```
  - Calls `Artisan::call($command)` and returns with flash `session('success')` or `session('error')`.
- **Security & Authorization**:
  - Route group in `routes/web.php` enforces `middleware(['auth', 'admin'])`.
  - Non-admin users are redirected to `/` by `App\Http\Middleware\IsAdmin`.
  - Whitelist prevents arbitrary command execution.
- **UI**:
  - `resources/views/admin/system.blade.php` displays grid of action buttons submitting POST requests with CSRF tokens.

---

## 6. Recommended Test Suites for Acceptance Criteria

To satisfy all Acceptance Criteria in `ORIGINAL_REQUEST.md`, downstream implementers should create the following test files:

### 6.1 Admin CRUD Test Suites (`tests/Feature/Admin/...`)
1. `tests/Feature/Admin/SettingTest.php`:
   - Admin can view settings index/edit form.
   - Admin can update restaurant name, contact info, hours, tax rate.
   - Non-admin cannot access settings routes (redirected).
2. `tests/Feature/Admin/PageTest.php`:
   - Admin can list, create, edit, update, delete custom pages.
   - Slug uniqueness validation.
3. `tests/Feature/Admin/NavigationTest.php`:
   - Admin can manage navigation menus (header/footer) and reorder/add/delete menu items.
4. `tests/Feature/Admin/CategoryTest.php`:
   - Admin can list, create, edit, update, and delete categories.
5. `tests/Feature/Admin/ProductTest.php`:
   - Admin can list, create, edit, update, and delete products (with category association and variant toggling).
6. `tests/Feature/Admin/ProductVariantTest.php`:
   - Admin can add, update, and remove variants for a product.
7. `tests/Feature/Admin/OrderTest.php`:
   - Admin can view order listings, filter by status, view order detail, and update order/payment status.
8. `tests/Feature/Admin/SystemCommandTest.php`:
   - Admin can execute allowed Artisan commands via `POST /admin/system/run`.
   - Unauthorized commands are rejected.

### 6.2 Frontend Customer Experience Test Suites (`tests/Feature/Frontend/...`)
1. `tests/Feature/Frontend/HomepageTest.php`:
   - `GET /` returns status 200.
   - Page displays dynamic restaurant settings (e.g. restaurant name, hero banner) loaded from database.
   - Dynamic navigation items rendered from database header menu.
2. `tests/Feature/Frontend/MenuTest.php`:
   - `GET /menu` returns status 200.
   - Displays active categories and available products with base prices.
   - Displays product variants and adjustments.
3. `tests/Feature/Frontend/PageDisplayTest.php`:
   - `GET /page/{slug}` displays published page content.
   - Returns 404 for unpublished or nonexistent pages.
4. `tests/Feature/Frontend/CartTest.php`:
   - User/Guest can add product (with or without selected variant) to session cart (`POST /cart/add`).
   - Verifies session contains cart items with correct quantity and calculated pricing.
   - User can update item quantities and remove items (`PATCH /cart/{id}`, `DELETE /cart/{id}`).
5. `tests/Feature/Frontend/CheckoutTest.php`:
   - User/Guest can view checkout page (`GET /checkout`).
   - Submitting checkout (`POST /checkout`) creates an `Order` record in database and associated `OrderItem` records, clears cart from session, and redirects to order confirmation view (`GET /order/confirmation/{id}`).

---

## 7. Next Steps for Implementation Agents

1. **Model Fillables & Relationships**: Update all 10 models in `app/Models/` with `$fillable`, casts, and Eloquent relationships.
2. **Factories Creation**: Implement factories for all models in `database/factories/` with realistic faker data.
3. **Database Seeder**: Enrich `database/seeders/DatabaseSeeder.php` with initial admin user, settings, navigation menus, default pages, and sample categories/products.
4. **Admin Controllers & Views**: Build complete CRUD controllers in `app/Http/Controllers/Admin/` and Blade views in `resources/views/admin/`.
5. **Frontend Controllers & Views**: Build customer controllers (`HomeController`, `MenuController`, `CartController`, `CheckoutController`, `PageController`) and frontend views.
6. **Feature Test Implementation**: Write comprehensive PHPUnit test classes in `tests/Feature/` covering all admin CRUDs and frontend flows.
