# Frontend UI, Views, Cart & Checkout Survey Report

**Project**: Self-Hosted Laravel 11 Restaurant Platform  
**Target Module**: Frontend UI, Customer-Facing Views, Session Cart & Checkout Flow  
**Author**: Explorer Survey Agent  
**Date**: 2026-08-25  

---

## 1. Executive Summary & Problem Scope

The objective of this investigation is to survey the frontend architecture, Blade view hierarchy, asset pipeline, shopping cart mechanics, checkout workflow, and zero-hardcoding requirements for the Laravel 11 restaurant platform.

The application is built on **Laravel 11.x**, utilizing **Tailwind CSS**, **Alpine.js**, **Laravel Breeze** components, and a pre-configured database schema. The frontend must deliver an intuitive, responsive, and reliable ordering experience for restaurant customers without relying on hardcoded assets, names, navigation links, or prices.

---

## 2. Current Codebase State Assessment

### 2.1 Asset Pipeline & Tooling
- **Vite & Tailwind CSS**:
  - `package.json` includes `tailwindcss: ^3.1.0` (with `@tailwindcss/vite: ^4.0.0`, `@tailwindcss/forms: ^0.5.2`, `alpinejs: ^3.4.2`, `postcss: ^8.4.31`, `autoprefixer: ^10.4.2`).
  - `tailwind.config.js` properly scans `./resources/views/**/*.blade.php` and `./vendor/laravel/framework/...`.
  - `resources/css/app.css` imports `@tailwind base; @tailwind components; @tailwind utilities;`.
  - `resources/js/app.js` initializes Alpine.js globally (`window.Alpine = Alpine; Alpine.start();`).
  - Pre-compiled assets and manifest exist in `public/build/manifest.json`, ensuring `@vite(['resources/css/app.css', 'resources/js/app.js'])` renders seamlessly without needing an active development server.

### 2.2 Existing Blade Layouts & Components
- **`resources/views/layouts/app.blade.php`**: Standard authenticated layout with Breeze top navigation bar.
- **`resources/views/layouts/guest.blade.php`**: Minimalist centered authentication layout.
- **`resources/views/components/`**: Provides reusable UI building blocks:
  - `modal.blade.php` (Alpine.js driven accessible modal with backdrop, keyboard navigation, and event listeners `open-modal` / `close-modal`).
  - `primary-button.blade.php`, `secondary-button.blade.php`, `danger-button.blade.php`.
  - `text-input.blade.php`, `input-label.blade.php`, `input-error.blade.php`.
  - `dropdown.blade.php`, `dropdown-link.blade.php`, `nav-link.blade.php`, `responsive-nav-link.blade.php`.
- **`resources/views/welcome.blade.php`**: Default boilerplate Laravel starter page; needs to be replaced with the customer-facing restaurant storefront or home view.

### 2.3 Database Schema & Model Analysis
The database schema defines 8 core application tables:
1. **`settings`** (`id`, `key` [unique], `value`, `type`, `timestamps`):
   - Key store configuration: `store_name`, `store_tagline`, `store_logo`, `store_phone`, `store_email`, `store_address`, `opening_hours`, `currency_symbol`, `tax_rate`, `delivery_fee`, `min_order_amount`.
2. **`pages`** (`id`, `title`, `slug` [unique], `content`, `meta_title`, `meta_description`, `og_image`, `is_published`, `timestamps`):
   - Dynamic CMS content (e.g. About Us, Contact, Delivery Terms, Privacy Policy).
3. **`navigation_menus`** (`id`, `name`, `location` [unique], `timestamps`):
   - Standard locations: `'header'` and `'footer'`.
4. **`navigation_items`** (`id`, `navigation_menu_id`, `label`, `url`, `page_id`, `order`, `target`, `timestamps`):
   - Polymorphic menu destinations (linked directly to a `Page` via `page_id` or external/internal `url`).
5. **`categories`** (`id`, `name`, `slug` [unique], `image`, `description`, `is_active`, `order`, `timestamps`):
   - Menu groupings (e.g. Starters, Main Dishes, Pizzas, Burgers, Desserts, Drinks).
6. **`products`** (`id`, `category_id`, `name`, `slug` [unique], `description`, `base_price`, `image`, `is_available`, `has_variants`, `timestamps`):
   - Base items with pricing and variant toggle flag.
7. **`product_variants`** (`id`, `product_id`, `name`, `type`, `price_adjustment`, `is_active`, `timestamps`):
   - Modifiers / variations (e.g. "Small (+$0.00)", "Medium (+$2.50)", "Large (+$5.00)").
8. **`orders`** (`id`, `user_id` [nullable], `customer_name`, `customer_email`, `customer_phone`, `order_type` [pickup/delivery], `delivery_address`, `order_notes`, `subtotal`, `tax`, `total`, `payment_method`, `payment_status`, `order_status`, `transaction_id`, `timestamps`):
   - Comprehensive customer order record.
9. **`order_items`** (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `unit_price`, `variants_selected` [JSON], `total_price`, `timestamps`):
   - Line items with snapshot product name, unit price, selected variants payload, and subtotal.

---

## 3. Frontend Architecture & View Specifications

```
+-----------------------------------------------------------------------------------+
|                            STOREFRONT LAYOUT (frontend.blade.php)                 |
|  - Dynamic Header (Logo, Settings, Navigation Menu [header], Cart Counter Badge) |
|  - Flash Notification Alerts (Success/Error/Cart feedback)                        |
+-----------------------------------------------------------------------------------+
       |                    |                    |                    |
       v                    v                    v                    v
+---------------+  +------------------+  +---------------+  +-------------------+
|   HOMEPAGE    |  |    MENU PAGE     |  |   CMS PAGES   |  |   SHOPPING CART   |
| (home.blade)  |  |  (menu.blade)    |  | (page.blade)  |  |   (cart.blade)    |
| - Hero Banner |  | - Category Tabs  |  | - Title/Body  |  | - Item Table      |
| - Featured    |  | - Product Cards  |  | - SEO Metas   |  | - Qty Stepper     |
|   Categories  |  | - Alpine Variant |  |               |  | - Subtotal/Totals |
| - Promo CTA   |  |   Modal & Calc   |  |               |  | - Proceed to Chk  |
+---------------+  +------------------+  +---------------+  +-------------------+
                                                                      |
                                                                      v
                                                            +-------------------+
                                                            |   CHECKOUT PAGE   |
                                                            | (checkout.blade)  |
                                                            | - Customer Form   |
                                                            | - Delivery/Pickup |
                                                            | - Payment Method  |
                                                            | - DB Transaction  |
                                                            +-------------------+
                                                                      |
                                                                      v
                                                            +-------------------+
                                                            | CONFIRMATION PAGE |
                                                            | (confirmation)    |
                                                            | - Order Details   |
                                                            | - Print Receipt   |
                                                            +-------------------+
```

### 3.1 Storefront Layout (`resources/views/layouts/frontend.blade.php`)
A dedicated frontend layout provides consistent header, footer, SEO metadata, and cart integration across all customer routes.

#### Components:
1. **Dynamic Header**:
   - Displays restaurant name and logo from `Setting::get('store_name')` and `Setting::get('store_logo')`.
   - Dynamic Navigation Links: Queries `NavigationMenu::where('location', 'header')->with(['items' => fn($q) => $q->orderBy('order'), 'items.page'])->first()`.
   - Links resolve dynamically: if `item->page_id` is set, link to `route('page.show', $item->page->slug)`; if `item->url` is set, link to `$item->url`.
   - Cart Indicator: Cart button with real-time item count badge (`count(session('cart', []))` or total quantity $\sum qty$).
   - User Auth Links: "Login / Register" or "My Orders / Admin Panel" if authenticated.
   - Mobile Hamburger Menu: Alpine.js toggle for responsive mobile drawer.
2. **Flash Messages**:
   - Renders session success/error banners (e.g. "Item added to cart", "Order placed successfully").
3. **Dynamic Footer**:
   - Brand description and tagline from settings (`store_tagline`).
   - Operating Hours, Phone, Email, and Physical Address from settings (`opening_hours`, `store_phone`, `store_email`, `store_address`).
   - Footer Navigation: Queries `NavigationMenu::where('location', 'footer')`.
   - Dynamic copyright notice (`&copy; {{ date('Y') }} {{ Setting::get('store_name', 'Restaurant') }}`).

---

### 3.2 Homepage View (`resources/views/frontend/home.blade.php`)
- **Hero Section**:
  - Highlighting restaurant welcome message, tagline, and prominent CTA buttons ("Browse Menu", "Order Now").
- **Featured Categories Section**:
  - Grid of active categories (`Category::where('is_active', true)->orderBy('order')->take(6)->get()`) with category image/icon, name, description, and link to filtered menu page (`/menu?category={slug}`).
- **Popular Dishes / Menu Highlights**:
  - Display top available products (`Product::where('is_available', true)->with('variants')->take(6)->get()`) in responsive cards with direct "Add to Cart" or "Customize" triggers.
- **Store Information / Why Choose Us**:
  - Operating hours, delivery options, and contact details from `settings`.

---

### 3.3 Menu Browsing Page (`resources/views/frontend/menu.blade.php`)
- **Category Filter Tabs / Sidebar**:
  - Sticky category pills or sidebar listing "All Items" plus each active category.
  - Active tab highlighting via URL query parameter (`?category=slug`) or Alpine.js active tab state.
- **Product Listing Grid**:
  - Responsive grid (1 col on mobile, 2 on tablet, 3-4 on desktop).
  - Product Card elements:
    * Image: Product image with fallback placeholder SVG.
    * Category badge.
    * Name & description snippet.
    * Dynamic price display:
      - Simple item: `{{ $currencySymbol }}{{ number_format($product->base_price, 2) }}`
      - Variant item: `From {{ $currencySymbol }}{{ number_format($product->base_price, 2) }}`
    * Action button:
      - Simple item (`has_variants == false`): "Add to Cart" form button.
      - Variant item (`has_variants == true`): "Choose Options" button triggering Alpine modal with product details.
- **Variant Selection Modal (`x-data` Alpine Modal)**:
  - Product details (name, image, description, base price).
  - Variant options grouped by type (e.g., Size, Crust, Extras) from `product_variants` table.
  - Live calculated total: `base_price + sum(selected_variant_price_adjustments) * quantity`.
  - Quantity selector (+ / -).
  - Submit button posting to `route('cart.add')`.

---

### 3.4 Session-Based Shopping Cart (`resources/views/frontend/cart.blade.php`)

#### Session Storage Structure:
Cart state is stored in `session('cart', [])` indexed by a unique item key:
```php
$cart = [
    'item_1_var_3' => [
        'item_key'     => 'item_1_var_3',
        'product_id'   => 1,
        'product_name' => 'Neapolitan Pizza',
        'variant_id'   => 3,
        'variant_name' => 'Large (+ $4.00)',
        'price'        => 18.50, // Base price ($14.50) + Variant adjustment ($4.00)
        'quantity'     => 2,
        'image'        => 'products/pizza.jpg',
    ],
    'item_2_simple' => [
        'item_key'     => 'item_2_simple',
        'product_id'   => 2,
        'product_name' => 'Garlic Bread',
        'variant_id'   => null,
        'variant_name' => null,
        'price'        => 5.00,
        'quantity'     => 1,
        'image'        => 'products/garlic_bread.jpg',
    ],
];
```

#### Calculations:
- **Item Total**: `$item['price'] * $item['quantity']`
- **Cart Subtotal**: $\sum (\text{Item Total})$
- **Tax Amount**: $\text{Subtotal} \times \text{tax\_rate}$ (where `tax_rate` is retrieved dynamically from `settings`, e.g. `0.08` for 8%)
- **Delivery Fee**: Retrieved dynamically from `settings` (`delivery_fee`, e.g. `5.00` or `0.00` for pickup)
- **Grand Total**: $\text{Subtotal} + \text{Tax} + \text{Delivery Fee}$

#### Cart Controller Actions:
- `CartController::index()`: Calculates subtotal, tax, and estimated totals; renders `frontend.cart`.
- `CartController::add(Request $request)`:
  - Validates `product_id` (required, exists:products,id), `quantity` (required, integer, min:1), `variant_id` (nullable, exists:product_variants,id).
  - Finds product, verifies `is_available`.
  - Computes unit price = `$product->base_price + ($variant ? $variant->price_adjustment : 0)`.
  - Constructs key: `$key = $variant ? "item_{$product->id}_var_{$variant->id}" : "item_{$product->id}_simple"`.
  - Increments quantity if key exists; creates new item if not.
  - Saves to session; redirects back with `session()->flash('success', 'Item added to cart!')`.
- `CartController::update(Request $request)`:
  - Validates `item_key` and `quantity` (min: 1, max: 99).
  - Updates item quantity in session.
- `CartController::remove($itemKey)`:
  - Removes item from `session('cart')`.
- `CartController::clear()`:
  - Clears `session()->forget('cart')`.

---

### 3.5 Checkout Flow & Order Processing (`resources/views/frontend/checkout.blade.php`)

#### Checkout Process:
1. **Empty Cart Guard**: If `session('cart')` is empty, redirect to `/menu` with warning notice.
2. **Customer Information Form**:
   - `customer_name` (string, required, pre-filled if authenticated).
   - `customer_email` (email, required, pre-filled if authenticated).
   - `customer_phone` (string, required).
   - `order_type` (`pickup` or `delivery`, required).
   - `delivery_address` (`x-show="orderType === 'delivery'"`, required if order_type is delivery).
   - `order_notes` (textarea, optional).
   - `payment_method` (`cash` / `card` / `online`, required).
3. **Order Summary Side-Card**:
   - Line items list with quantity, variant name, and price.
   - Subtotal, Tax breakdown, Delivery Fee, and Grand Total.
4. **Order Placement (`POST /checkout` -> `CheckoutController::store`)**:
   - Validates input.
   - Executes database transaction (`DB::transaction`):
     1. Creates record in `orders` table:
        ```php
        $order = Order::create([
            'user_id'          => Auth::id(),
            'customer_name'    => $validated['customer_name'],
            'customer_email'   => $validated['customer_email'],
            'customer_phone'   => $validated['customer_phone'],
            'order_type'       => $validated['order_type'],
            'delivery_address' => $validated['order_type'] === 'delivery' ? $validated['delivery_address'] : null,
            'order_notes'      => $validated['order_notes'] ?? null,
            'subtotal'         => $subtotal,
            'tax'              => $tax,
            'total'            => $total,
            'payment_method'   => $validated['payment_method'],
            'payment_status'   => 'pending',
            'order_status'     => 'new',
            'transaction_id'   => 'TXN-' . strtoupper(Str::random(8)),
        ]);
        ```
     2. Creates records in `order_items` table:
        ```php
        foreach ($cart as $item) {
            OrderItem::create([
                'order_id'          => $order->id,
                'product_id'        => $item['product_id'],
                'product_name'      => $item['product_name'],
                'quantity'          => $item['quantity'],
                'unit_price'        => $item['price'],
                'variants_selected' => !empty($item['variant_name']) ? ['name' => $item['variant_name'], 'variant_id' => $item['variant_id'] ?? null] : null,
                'total_price'       => $item['price'] * $item['quantity'],
            ]);
        }
        ```
     3. Clears session cart: `session()->forget('cart')`.
     4. Redirects to `route('order.confirmation', $order->id)`.

5. **Order Confirmation Page (`resources/views/frontend/order-confirmation.blade.php`)**:
   - Order Reference `#{{ $order->id }}` and Transaction ID.
   - Status badge (`new` / "Order Received").
   - Pickup/Delivery summary and instructions.
   - Customer details.
   - Full receipt breakdown table with products, variants, quantities, subtotal, tax, and grand total.
   - "Print Receipt" button (using `window.print()`).
   - "Order More / Back to Menu" button.

---

### 3.6 Dynamic CMS Pages (`resources/views/frontend/page.blade.php`)
- Route: `GET /page/{slug}` -> `PageController::show($slug)`
- Queries `Page::where('slug', $slug)->where('is_published', true)->firstOrFail()`.
- Renders page title, rich HTML content (`{!! $page->content !!}` with appropriate formatting/prose classes), meta title, meta description, and social OG tags.

---

## 4. Zero-Hardcoding Guidelines & Compliance Matrix

| UI Element / Field | Source of Truth | Fallback Strategy |
|---|---|---|
| Restaurant / Store Name | `Setting::get('store_name')` | `config('app.name', 'Restaurant')` |
| Store Logo / Brand Image | `Setting::get('store_logo')` | Default SVG Logo component |
| Tagline & Mission | `Setting::get('store_tagline')` | Dynamic database content |
| Contact Phone & Email | `Setting::get('store_phone')`, `store_email` | Dynamic database content |
| Physical Store Address | `Setting::get('store_address')` | Dynamic database content |
| Operating / Opening Hours | `Setting::get('opening_hours')` | Dynamic database content |
| Currency Symbol | `Setting::get('currency_symbol')` | `$` |
| Tax Rate Percentage | `Setting::get('tax_rate')` | `0.00` |
| Delivery Fee Amount | `Setting::get('delivery_fee')` | `0.00` |
| Header Navigation Items | `NavigationMenu::where('location', 'header')` | Query from DB; empty if none |
| Footer Navigation Items | `NavigationMenu::where('location', 'footer')` | Query from DB; empty if none |
| Menu Categories | `Category::where('is_active', true)` | Query from DB |
| Menu Products & Base Prices | `Product::where('is_available', true)` | Query from DB |
| Product Variants & Adjustments | `ProductVariant::where('is_active', true)`| Query from DB |
| CMS Static Pages | `Page::where('is_published', true)` | Query from DB |

---

## 5. Implementation Roadmap for Implementer Agents

### Step 1: Core Models & Settings Helper
1. Update `Setting.php` with static helper methods `get($key, $default = null)` and `set($key, $value, $type = 'string')` with caching support.
2. Update all Eloquent Models (`Category`, `Product`, `ProductVariant`, `Order`, `OrderItem`, `NavigationMenu`, `NavigationItem`, `Page`) with appropriate `$fillable`, `$casts`, and relationships (`hasMany`, `belongsTo`).

### Step 2: Global View Sharing / View Composer
1. In `AppServiceProvider::boot()`, share dynamic `$settings`, `$headerMenu`, and `$footerMenu` with all views (or create a dedicated ViewComposer) to ensure zero hardcoding across all layout files.

### Step 3: Frontend Controllers & Routes
1. Create `app/Http/Controllers/Frontend/HomeController.php`
2. Create `app/Http/Controllers/Frontend/MenuController.php`
3. Create `app/Http/Controllers/Frontend/CartController.php`
4. Create `app/Http/Controllers/Frontend/CheckoutController.php`
5. Create `app/Http/Controllers/Frontend/PageController.php`
6. Register routes in `routes/web.php`.

### Step 4: Frontend Blade Views
1. Create `resources/views/layouts/frontend.blade.php` (Header, Nav, Cart Counter, Flash Banners, Footer).
2. Create `resources/views/frontend/home.blade.php` (Hero, Categories, Highlights).
3. Create `resources/views/frontend/menu.blade.php` (Category filter tabs, product cards, Alpine variant selection modal).
4. Create `resources/views/frontend/cart.blade.php` (Cart items table, quantity controls, calculation summary, checkout CTA).
5. Create `resources/views/frontend/checkout.blade.php` (Customer details, order type switch, delivery address, order summary).
6. Create `resources/views/frontend/order-confirmation.blade.php` (Order receipt, status, print button).
7. Create `resources/views/frontend/page.blade.php` (Dynamic CMS pages).

### Step 5: Automated Testing Suite
1. Create `tests/Feature/FrontendTest.php`:
   - Test homepage returns 200 and displays dynamic settings from DB.
   - Test menu page returns 200 and lists active categories and products.
   - Test dynamic CMS page returns 200.
2. Create `tests/Feature/CartTest.php`:
   - Test adding simple product to session cart.
   - Test adding product with variant to session cart.
   - Test updating quantity and removing items from cart.
   - Test subtotal and tax calculation.
3. Create `tests/Feature/CheckoutTest.php`:
   - Test placing pickup order creates `Order` and `OrderItem` records.
   - Test placing delivery order with delivery address.
   - Test session cart is cleared upon successful order placement.
   - Test confirmation screen shows order details.

---

## 6. Summary

The frontend architecture surveyed above is completely modular, relies 100% on database-driven content with zero hardcoding, adheres to standard Tailwind CSS styling, utilizes lightweight Alpine.js interactions for modals and dynamic calculations, and seamlessly interfaces with the underlying database schema.
