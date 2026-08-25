# Backend Architecture & Admin Requirements Survey Report

**Project:** Laravel 11 Restaurant & Food Ordering Platform  
**Target Workspace:** `i:\Client Restaurant`  
**Date:** 2026-08-25  
**Author:** Explorer Subagent (Backend Architecture & Admin Requirements)  

---

## 1. Executive Summary

The project is a self-hosted restaurant e-commerce and menu management platform built on Laravel 11/12 with Laravel Breeze (Blade + Tailwind CSS + Alpine.js). 

### Key Architectural Findings:
1. **Database Schema & Migrations:** All 10 core tables are fully designed and migrated (`users`, `settings`, `pages`, `navigation_menus`, `navigation_items`, `categories`, `products`, `product_variants`, `orders`, `order_items`).
2. **Security & Middleware:** `IsAdmin` middleware is implemented and registered under the alias `'admin'` in `bootstrap/app.php`. User authentication is powered by Laravel Breeze with an `is_admin` boolean flag on the `users` table.
3. **Zero-Terminal System Manager:** `SystemCommandController` and its view `resources/views/admin/system.blade.php` are implemented, enabling admins to execute `cache:clear`, `config:clear`, `route:clear`, `view:clear`, `optimize:clear`, and `storage:link` from the UI without SSH.
4. **Current Implementation Gaps:**
   - **Eloquent Models:** 9 out of 10 models (`Category`, `NavigationItem`, `NavigationMenu`, `Order`, `OrderItem`, `Page`, `Product`, `ProductVariant`, `Setting`) are empty skeleton classes without `$fillable`, relationships, casts, or helper methods. `User` is missing `is_admin` casting and orders relationship.
   - **Admin Controllers:** `DashboardController`, `SettingController`, `PageController`, `MenuController`, `ProductController`, and `OrderController` are empty shells. A dedicated `CategoryController` and `NavigationController` are missing.
   - **Admin Routes:** Only `/admin`, `/admin/system`, and `/admin/system/run` are defined in `routes/web.php`. None of the domain CRUD routes are registered.
   - **Admin Views:** Only `admin/system.blade.php` exists. Missing all CRUD views (Dashboard, Settings, Pages, Navigation, Categories, Products, Orders) and admin navigation links.
   - **Seeders:** `DatabaseSeeder.php` only creates a single non-admin test user; no initial settings, pages, categories, products, or admin users are seeded.

---

## 2. Database Schema & Eloquent Model Specifications

### 2.1 Table: `settings`
- **Schema Columns:**
  - `id`: `bigint unsigned AUTO_INCREMENT PRIMARY KEY`
  - `key`: `varchar(255) UNIQUE NOT NULL`
  - `value`: `text NULL`
  - `type`: `varchar(255) DEFAULT 'string'` ('string', 'text', 'boolean', 'integer', 'float', 'json', 'image')
  - `created_at`, `updated_at`: `timestamp NULL`
- **Model `App\Models\Setting` Requirements:**
  - `$fillable = ['key', 'value', 'type']`
  - Static helper `Setting::get(string $key, $default = null)`: Fetches and type-casts value.
  - Static helper `Setting::set(string $key, $value, string $type = 'string')`: Upserts setting.
  - Static helper `Setting::allGrouped()`: Fetches all settings as key-value pairs with caching.
  - Global helper function `setting($key, $default = null)` for easy Blade access.
- **Core Platform Settings Directory:**
  - **Restaurant Branding:** `site_name`, `site_tagline`, `site_logo`, `site_favicon`
  - **Contact & Hours:** `contact_email`, `contact_phone`, `contact_address`, `opening_hours` (e.g. Mon-Sun schedule)
  - **Ordering & Pricing:** `currency_symbol` (default `$`), `currency_code` (default `USD`), `tax_rate_percent` (e.g. `8.25`), `delivery_fee` (e.g. `5.00`), `minimum_order_amount` (e.g. `15.00`), `enable_pickup` (boolean), `enable_delivery` (boolean)
  - **Social Links:** `facebook_url`, `instagram_url`, `twitter_url`, `yelp_url`
  - **Third-Party Delivery Outbound Links:** `doordash_url`, `ubereats_url`, `grubhub_url`
  - **Payment Credentials:** `stripe_enabled`, `stripe_publishable_key`, `stripe_secret_key`, `square_enabled`, `square_application_id`, `square_access_token`, `square_location_id`, `cod_enabled`

---

### 2.2 Table: `pages`
- **Schema Columns:**
  - `id`: `bigint unsigned AUTO_INCREMENT PRIMARY KEY`
  - `title`: `varchar(255) NOT NULL`
  - `slug`: `varchar(255) UNIQUE NOT NULL`
  - `content`: `longtext NULL` (rich text / HTML)
  - `meta_title`: `varchar(255) NULL`
  - `meta_description`: `text NULL`
  - `og_image`: `varchar(255) NULL`
  - `is_published`: `tinyint(1) DEFAULT 1`
  - `created_at`, `updated_at`: `timestamp NULL`
- **Model `App\Models\Page` Requirements:**
  - `$fillable = ['title', 'slug', 'content', 'meta_title', 'meta_description', 'og_image', 'is_published']`
  - `$casts = ['is_published' => 'boolean']`
  - Relationships: `hasMany(NavigationItem::class)`
  - Scope: `scopePublished($query)` -> `where('is_published', true)`

---

### 2.3 Tables: `navigation_menus` and `navigation_items`
- **Schema `navigation_menus`:**
  - `id`: `bigint unsigned AUTO_INCREMENT PRIMARY KEY`
  - `name`: `varchar(255) NOT NULL` (e.g. "Main Header Menu", "Footer Menu")
  - `location`: `varchar(255) UNIQUE NOT NULL` (e.g. "header", "footer")
  - `created_at`, `updated_at`: `timestamp NULL`
- **Schema `navigation_items`:**
  - `id`: `bigint unsigned AUTO_INCREMENT PRIMARY KEY`
  - `navigation_menu_id`: `foreignId constrained onDelete cascade`
  - `label`: `varchar(255) NOT NULL`
  - `url`: `varchar(255) NULL`
  - `page_id`: `foreignId NULL constrained onDelete set null`
  - `order`: `int DEFAULT 0`
  - `target`: `varchar(255) DEFAULT '_self'` ('_self', '_blank')
  - `created_at`, `updated_at`: `timestamp NULL`
- **Model `App\Models\NavigationMenu`:**
  - `$fillable = ['name', 'location']`
  - Relationships: `hasMany(NavigationItem::class)->orderBy('order')`
  - Static helper: `NavigationMenu::getByLocation(string $location)` with eager loaded items and pages.
- **Model `App\Models\NavigationItem`:**
  - `$fillable = ['navigation_menu_id', 'label', 'url', 'page_id', 'order', 'target']`
  - `$casts = ['order' => 'integer']`
  - Relationships: `belongsTo(NavigationMenu::class)`, `belongsTo(Page::class)`
  - Accessor `getResolvedUrlAttribute()`: Returns `$this->url ?: ($this->page ? route('pages.show', $this->page->slug) : '#')`

---

### 2.4 Table: `categories`
- **Schema Columns:**
  - `id`: `bigint unsigned AUTO_INCREMENT PRIMARY KEY`
  - `name`: `varchar(255) NOT NULL`
  - `slug`: `varchar(255) UNIQUE NOT NULL`
  - `image`: `varchar(255) NULL`
  - `description`: `text NULL`
  - `is_active`: `tinyint(1) DEFAULT 1`
  - `order`: `int DEFAULT 0`
  - `created_at`, `updated_at`: `timestamp NULL`
- **Model `App\Models\Category`:**
  - `$fillable = ['name', 'slug', 'image', 'description', 'is_active', 'order']`
  - `$casts = ['is_active' => 'boolean', 'order' => 'integer']`
  - Relationships: `hasMany(Product::class)->orderBy('name')`
  - Scopes: `scopeActive($query)`, `scopeOrdered($query)`

---

### 2.5 Table: `products`
- **Schema Columns:**
  - `id`: `bigint unsigned AUTO_INCREMENT PRIMARY KEY`
  - `category_id`: `foreignId constrained onDelete cascade`
  - `name`: `varchar(255) NOT NULL`
  - `slug`: `varchar(255) UNIQUE NOT NULL`
  - `description`: `text NULL`
  - `base_price`: `decimal(10,2) NOT NULL`
  - `image`: `varchar(255) NULL`
  - `is_available`: `tinyint(1) DEFAULT 1`
  - `has_variants`: `tinyint(1) DEFAULT 0`
  - `created_at`, `updated_at`: `timestamp NULL`
- **Model `App\Models\Product`:**
  - `$fillable = ['category_id', 'name', 'slug', 'description', 'base_price', 'image', 'is_available', 'has_variants']`
  - `$casts = ['base_price' => 'decimal:2', 'is_available' => 'boolean', 'has_variants' => 'boolean']`
  - Relationships: `belongsTo(Category::class)`, `hasMany(ProductVariant::class)->orderBy('price_adjustment')`, `hasMany(OrderItem::class)`
  - Scopes: `scopeAvailable($query)`
  - Accessors: `getFormattedPriceAttribute()`, `getImageUrlAttribute()`

---

### 2.6 Table: `product_variants`
- **Schema Columns:**
  - `id`: `bigint unsigned AUTO_INCREMENT PRIMARY KEY`
  - `product_id`: `foreignId constrained onDelete cascade`
  - `name`: `varchar(255) NOT NULL` (e.g. "Small 10\"", "Large 16\"", "Extra Cheese", "Gluten Free Crust")
  - `type`: `varchar(255) DEFAULT 'size'` ('size', 'addon', 'option', 'spice_level')
  - `price_adjustment`: `decimal(10,2) DEFAULT 0.00`
  - `is_active`: `tinyint(1) DEFAULT 1`
  - `created_at`, `updated_at`: `timestamp NULL`
- **Model `App\Models\ProductVariant`:**
  - `$fillable = ['product_id', 'name', 'type', 'price_adjustment', 'is_active']`
  - `$casts = ['price_adjustment' => 'decimal:2', 'is_active' => 'boolean']`
  - Relationships: `belongsTo(Product::class)`
  - Scopes: `scopeActive($query)`

---

### 2.7 Tables: `orders` and `order_items`
- **Schema `orders`:**
  - `id`: `bigint unsigned AUTO_INCREMENT PRIMARY KEY`
  - `user_id`: `foreignId NULL constrained onDelete set null`
  - `customer_name`: `varchar(255) NOT NULL`
  - `customer_email`: `varchar(255) NOT NULL`
  - `customer_phone`: `varchar(255) NULL`
  - `order_type`: `varchar(255) DEFAULT 'pickup'` ('pickup', 'delivery')
  - `delivery_address`: `text NULL`
  - `order_notes`: `text NULL`
  - `subtotal`: `decimal(10,2) NOT NULL`
  - `tax`: `decimal(10,2) DEFAULT 0.00`
  - `total`: `decimal(10,2) NOT NULL`
  - `payment_method`: `varchar(255) NOT NULL` ('stripe', 'square', 'cod', 'cash')
  - `payment_status`: `varchar(255) DEFAULT 'pending'` ('pending', 'paid', 'failed', 'refunded')
  - `order_status`: `varchar(255) DEFAULT 'new'` ('new', 'preparing', 'ready', 'completed', 'cancelled')
  - `transaction_id`: `varchar(255) NULL`
  - `created_at`, `updated_at`: `timestamp NULL`
- **Schema `order_items`:**
  - `id`: `bigint unsigned AUTO_INCREMENT PRIMARY KEY`
  - `order_id`: `foreignId constrained onDelete cascade`
  - `product_id`: `foreignId NULL constrained onDelete set null`
  - `product_name`: `varchar(255) NOT NULL`
  - `quantity`: `int NOT NULL`
  - `unit_price`: `decimal(10,2) NOT NULL`
  - `variants_selected`: `json NULL` (e.g. `[{"name":"Large 16\"","type":"size","price_adjustment":4.00}]`)
  - `total_price`: `decimal(10,2) NOT NULL`
  - `created_at`, `updated_at`: `timestamp NULL`
- **Model `App\Models\Order`:**
  - `$fillable = ['user_id', 'customer_name', 'customer_email', 'customer_phone', 'order_type', 'delivery_address', 'order_notes', 'subtotal', 'tax', 'total', 'payment_method', 'payment_status', 'order_status', 'transaction_id']`
  - `$casts = ['subtotal' => 'decimal:2', 'tax' => 'decimal:2', 'total' => 'decimal:2']`
  - Relationships: `belongsTo(User::class)`, `hasMany(OrderItem::class)`
  - Accessors: `getOrderNumberAttribute()` -> `#ORD-` . str_pad($this->id, 5, '0', STR_PAD_LEFT)
- **Model `App\Models\OrderItem`:**
  - `$fillable = ['order_id', 'product_id', 'product_name', 'quantity', 'unit_price', 'variants_selected', 'total_price']`
  - `$casts = ['quantity' => 'integer', 'unit_price' => 'decimal:2', 'total_price' => 'decimal:2', 'variants_selected' => 'array']`
  - Relationships: `belongsTo(Order::class)`, `belongsTo(Product::class)`

---

### 2.8 Table: `users`
- **Schema Columns:** `id`, `name`, `email`, `email_verified_at`, `password`, `is_admin`, `remember_token`, `timestamps`
- **Model `App\Models\User` Updates Needed:**
  - Update `#[Fillable(['name', 'email', 'password', 'is_admin'])]` or `$fillable` property.
  - Add `'is_admin' => 'boolean'` to `casts()`.
  - Add relationship: `hasMany(Order::class)`.

---

## 3. Backend Route Architecture & Middleware Map

All Admin routes must reside inside `routes/web.php`:
```php
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // 1. Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

    // 3. Pages CRUD
    Route::resource('pages', PageController::class);

    // 4. Navigation Menus & Items
    Route::get('/navigation', [NavigationController::class, 'index'])->name('navigation.index');
    Route::post('/navigation/menus', [NavigationController::class, 'storeMenu'])->name('navigation.menus.store');
    Route::post('/navigation/items', [NavigationController::class, 'storeItem'])->name('navigation.items.store');
    Route::put('/navigation/items/{item}', [NavigationController::class, 'updateItem'])->name('navigation.items.update');
    Route::delete('/navigation/items/{item}', [NavigationController::class, 'destroyItem'])->name('navigation.items.destroy');
    Route::post('/navigation/reorder', [NavigationController::class, 'reorder'])->name('navigation.reorder');

    // 5. Categories CRUD
    Route::resource('categories', CategoryController::class);

    // 6. Products CRUD & Variants Management
    Route::resource('products', ProductController::class);
    Route::post('/products/{product}/variants', [ProductController::class, 'storeVariant'])->name('products.variants.store');
    Route::delete('/variants/{variant}', [ProductController::class, 'destroyVariant'])->name('variants.destroy');

    // 7. Orders Management
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::get('/orders/{order}/print', [OrderController::class, 'printReceipt'])->name('orders.print');
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');

    // 8. Zero-Terminal System Manager
    Route::get('/system', [SystemCommandController::class, 'index'])->name('system');
    Route::post('/system/run', [SystemCommandController::class, 'runCommand'])->name('system.run');
});
```

---

## 4. Admin Controllers & CRUD Blueprint

| Module | Controller | Action Methods | Key Responsibilities |
|---|---|---|---|
| **Dashboard** | `DashboardController` | `index` | Aggregate revenue (total & today), order counts by status (new, preparing, ready), total products, low-availability count. Render summary analytics cards and recent orders table. |
| **Settings** | `SettingController` | `index`, `update` | Form tabs for Restaurant Info, Operating Hours, Tax/Delivery Fees, Social Links, Payment Credentials. Handle file uploads for `site_logo` and `site_favicon` to `public/settings`. |
| **Pages** | `PageController` | `index`, `create`, `store`, `edit`, `update`, `destroy` | List pages with published status badge. Form for title, auto-slug generation, HTML/content body, SEO meta tags, `og_image` upload. |
| **Navigation** | `NavigationController` | `index`, `storeMenu`, `storeItem`, `updateItem`, `destroyItem`, `reorder` | Manage header & footer menus. Add items linked to custom URLs or dynamically mapped to published `pages`. Order sorting. |
| **Categories** | `CategoryController` | `index`, `create`, `store`, `edit`, `update`, `destroy` | Category listing with product count. Image upload to `public/categories`. Active/inactive toggle, sort order. |
| **Products** | `ProductController` | `index`, `create`, `store`, `edit`, `update`, `destroy`, `storeVariant`, `destroyVariant` | Product listing with category filter and search. Create/edit product with category association, base price, image upload to `public/products`, availability toggle, and dynamic variant rows (size/addon with price adjustment). |
| **Orders** | `OrderController` | `index`, `show`, `updateStatus`, `printReceipt`, `destroy` | Filter orders by `order_status` ('new', 'preparing', 'ready', 'completed', 'cancelled'), `payment_status`, date range. Detailed order receipt view with item breakdown, customer info, address, delivery type. Fast status updater and printable thermal/kitchen receipt view. |
| **System Manager** | `SystemCommandController` | `index`, `runCommand` | (Already implemented) Allows running safe Artisan commands (`cache:clear`, `config:clear`, `route:clear`, `view:clear`, `optimize:clear`, `storage:link`). |

---

## 5. Validation Rules & File Upload Handling

### 5.1 Validation Matrix
- **Category:**
  - `name`: `['required', 'string', 'max:255']`
  - `slug`: `['nullable', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($category?->id)]`
  - `description`: `['nullable', 'string']`
  - `image`: `['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048']`
  - `is_active`: `['boolean']`
  - `order`: `['nullable', 'integer', 'min:0']`
- **Product:**
  - `category_id`: `['required', 'exists:categories,id']`
  - `name`: `['required', 'string', 'max:255']`
  - `slug`: `['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($product?->id)]`
  - `description`: `['nullable', 'string']`
  - `base_price`: `['required', 'numeric', 'min:0']`
  - `image`: `['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048']`
  - `is_available`: `['boolean']`
  - `has_variants`: `['boolean']`
  - `variants.*.name`: `['required_with:variants', 'string', 'max:255']`
  - `variants.*.type`: `['required_with:variants', 'in:size,addon,option,spice_level']`
  - `variants.*.price_adjustment`: `['required_with:variants', 'numeric']`
- **Page:**
  - `title`: `['required', 'string', 'max:255']`
  - `slug`: `['nullable', 'string', 'max:255', Rule::unique('pages', 'slug')->ignore($page?->id)]`
  - `content`: `['nullable', 'string']`
  - `meta_title`: `['nullable', 'string', 'max:255']`
  - `meta_description`: `['nullable', 'string', 'max:500']`
  - `og_image`: `['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048']`
  - `is_published`: `['boolean']`
- **Navigation Item:**
  - `navigation_menu_id`: `['required', 'exists:navigation_menus,id']`
  - `label`: `['required', 'string', 'max:255']`
  - `url`: `['nullable', 'string', 'max:255']`
  - `page_id`: `['nullable', 'exists:pages,id']`
  - `order`: `['nullable', 'integer', 'min:0']`
  - `target`: `['required', 'in:_self,_blank']`
- **Order Status Update:**
  - `order_status`: `['required', 'in:new,preparing,ready,completed,cancelled']`
  - `payment_status`: `['required', 'in:pending,paid,failed,refunded']`

### 5.2 Image & Storage Handling
- Disk: `public` (`storage/app/public/...`)
- Symbolic Link: `public/storage` linked via `php artisan storage:link` (can be triggered directly through the Zero-Terminal interface if needed).
- Replacement rule: When a new image is uploaded on update, the previous file should be purged with `Storage::disk('public')->delete($oldPath)`.

---

## 6. Seeders & Initial Data Blueprint

To ensure dynamic, zero-hardcoded frontend & admin functionality, `DatabaseSeeder.php` should orchestrate:
1. **Admin & Customer Users:**
   - Admin: `admin@restaurant.com` / `password` (`is_admin = true`)
   - Customer: `customer@restaurant.com` / `password` (`is_admin = false`)
2. **Default Settings:**
   - Restaurant branding: "Bella Napoli Ristorante", tagline "Authentic Italian Wood-Fired Pizza & Pasta", address, phone `(555) 234-5678`, email `contact@bellanapoli.com`, opening hours `Mon-Sun: 11:00 AM - 10:00 PM`.
   - Currency: `$`, `USD`, Tax rate: `8.25%`, Delivery fee: `$4.99`, Minimum order: `$15.00`, Pickup enabled: `1`, Delivery enabled: `1`.
   - Social links and DoorDash/UberEats links.
3. **Default Pages:**
   - "About Us" (`slug: about-us`, rich story about the restaurant).
   - "Contact & Location" (`slug: contact-us`).
   - "Terms of Service" (`slug: terms`).
   - "Privacy Policy" (`slug: privacy`).
4. **Default Navigation Menus & Items:**
   - `header` menu: Home (`/`), Menu (`/menu`), About Us (linked to page `about-us`), Contact (linked to page `contact-us`).
   - `footer` menu: Menu (`/menu`), Terms (`terms`), Privacy (`privacy`), About Us (`about-us`).
5. **Categories & Products with Variants:**
   - *Appetizers:* Bruschetta Al Pomodoro ($8.50), Calamari Fritti ($12.00), Garlic Bread ($6.00).
   - *Wood-Fired Pizzas:* Margherita Pizza ($14.00, variants: Small 10" +$0, Medium 12" +$3.50, Large 16" +$6.00, Extra Mozzarella +$2.00), Pepperoni Rustica ($16.50, variants: Medium +$0, Large +$4.00), Quattro Formaggi ($17.00).
   - *Handmade Pastas:* Fettuccine Alfredo ($15.50, variants: Add Grilled Chicken +$4.00, Add Shrimp +$6.00), Spaghetti Carbonara ($16.00), Lasagna Bolognese ($17.50).
   - *Desserts:* Classic Tiramisu ($8.00), Cannoli Siciliani ($7.00), Panna Cotta ($7.50).
   - *Beverages:* Italian Soda ($3.50), San Pellegrino ($4.00), Espresso ($3.00).
6. **Sample Orders:**
   - Create 3-5 sample orders across different statuses (`new`, `preparing`, `ready`, `completed`) with itemized variants to enable immediate testing of admin order workflows.

---

## 7. Automated Testing Plan for Backend & Admin

1. **Admin Authorization (`tests/Feature/Admin/AdminAccessTest.php`):**
   - Unauthenticated guest redirected to login for any `/admin/*` route.
   - Standard user (`is_admin = false`) redirected to `/` for any `/admin/*` route.
   - Admin user (`is_admin = true`) receives 200 OK for `/admin`, `/admin/settings`, `/admin/categories`, `/admin/products`, `/admin/pages`, `/admin/navigation`, `/admin/orders`, `/admin/system`.
2. **Category CRUD (`tests/Feature/Admin/CategoryCrudTest.php`):**
   - Test category creation, validation errors on empty name, image upload, updating, deleting.
3. **Product & Variant CRUD (`tests/Feature/Admin/ProductCrudTest.php`):**
   - Test product creation with category, base price, variant association.
   - Test product update, toggle availability, variant addition/deletion, delete product.
4. **Page & Navigation CRUD (`tests/Feature/Admin/PageAndNavigationTest.php`):**
   - Test page creation with auto-slug, published status toggle, update, delete.
   - Test navigation menu item creation linked to page or custom URL, reordering.
5. **Setting Management (`tests/Feature/Admin/SettingTest.php`):**
   - Test updating store settings (restaurant name, tax rate, delivery fee, branding).
   - Verify updated settings reflect in `Setting::get()` and helper.
6. **Order Management (`tests/Feature/Admin/OrderManagementTest.php`):**
   - Test order listing with filters.
   - Test viewing single order details with itemized variants.
   - Test updating order status (`new` -> `preparing` -> `ready` -> `completed`) and payment status.
   - Test order deletion.

---

## 8. Implementation Order & Recommendations for Next Agents

1. **Phase 1: Foundation Models & Seeders**
   - Implement `$fillable`, `$casts`, relationships, and helpers in all 9 Eloquent models and `User`.
   - Create setting helper / service provider.
   - Populate `DatabaseSeeder.php` with rich sample data and default admin credentials.
2. **Phase 2: Admin Layout & Navigation Enhancement**
   - Update `resources/views/layouts/navigation.blade.php` or create dedicated admin navigation with active link highlighting for Dashboard, Orders, Categories, Products, Pages, Navigation, Settings, System Manager.
3. **Phase 3: Admin Controllers & Blade Views**
   - Build `CategoryController` + views (`admin/categories/index`, `create`, `edit`).
   - Build `ProductController` + views (`admin/products/index`, `create`, `edit` with inline variant management).
   - Build `PageController` + views (`admin/pages/index`, `create`, `edit`).
   - Build `NavigationController` + views (`admin/navigation/index`).
   - Build `SettingController` + view (`admin/settings/index`).
   - Build `OrderController` + views (`admin/orders/index`, `show`, `print`).
   - Build `DashboardController` + view (`admin/dashboard.blade.php`).
4. **Phase 4: Route Registration & Testing**
   - Register all admin routes in `routes/web.php`.
   - Write comprehensive PHPUnit feature tests to achieve 100% passing test suite across all CRUD operations.
