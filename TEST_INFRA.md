# Opaque-Box Test Infrastructure Specification

**Project**: Laravel 11 Restaurant Platform (Admin CRUDs & Frontend UI)  
**Test Framework**: PHPUnit 12.x on Laravel 11 Testing Harness  
**Database**: SQLite In-Memory (`:memory:`) with `RefreshDatabase`  
**Test Suite Location**: `tests/Feature/E2E/`

---

## 1. Overview & Architectural Principles

This document defines the test architecture, execution strategy, and test tier hierarchy for the self-hosted Laravel 11 restaurant platform. The testing harness is designed to execute as an **opaque-box test suite**, testing the application strictly through HTTP endpoints, Session interactions, Database state mutations, and Eloquent interface contracts.

### Core Testing Axioms
1. **Isolation**: Every test class uses `Illuminate\Foundation\Testing\RefreshDatabase`. Tests run inside isolated SQLite transactions in-memory (`:memory:`), ensuring zero test order dependency or leftover state pollution.
2. **Opaque-Box Verification**: Tests make HTTP requests (`$this->get()`, `$this->post()`, `$this->put()`, `$this->patch()`, `$this->delete()`), inspect response status codes, verify redirected locations, assert view payloads and rendered strings, and assert database mutations (`assertDatabaseHas`, `assertDatabaseMissing`).
3. **Zero Hardcoding Enforcement**: Tests verify that store branding, contact information, navigation menus, product catalog, categories, pricing, tax rates, and CMS pages are dynamically rendered from the database without hardcoded fallbacks.
4. **Deterministic Calculations**: Tax, delivery fees, and order totals are validated against exact mathematical calculations based on dynamic `Setting` records.

---

## 2. Environment Configuration (`phpunit.xml`)

Tests execute under the standard Laravel test environment defined in `phpunit.xml`:

```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="APP_MAINTENANCE_DRIVER" value="file"/>
    <env name="BCRYPT_ROUNDS" value="4"/>
    <env name="CACHE_STORE" value="array"/>
    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>
    <env name="MAIL_MAILER" value="array"/>
    <env name="QUEUE_CONNECTION" value="sync"/>
    <env name="SESSION_DRIVER" value="array"/>
</php>
```

- **In-Memory SQLite**: Migrations are run on-the-fly during test initialization.
- **Array Session Driver**: Enables reliable simulation of shopping cart state across test requests using `$this->withSession(['cart' => ...])` and `$this->session(['cart' => ...])`.
- **Fast Hashing**: `BCRYPT_ROUNDS=4` accelerates user authentication and admin login tests.

---

## 3. Test Suite Taxonomy (4-Tier Hierarchy)

The E2E test suite is organized into 4 distinct testing tiers under `tests/Feature/E2E/`:

```
tests/Feature/E2E/
├── Tier1_FeatureCoverage/
│   ├── AdminSettingsTest.php
│   ├── AdminPagesTest.php
│   ├── AdminNavigationTest.php
│   ├── AdminCategoriesTest.php
│   ├── AdminProductsTest.php
│   ├── AdminProductVariantsTest.php
│   ├── AdminOrdersTest.php
│   ├── FrontendHomeTest.php
│   ├── FrontendMenuTest.php
│   ├── FrontendCartTest.php
│   └── FrontendCheckoutTest.php
├── Tier2_BoundaryAndCornerCases/
│   ├── AdminBoundaryTest.php
│   ├── CatalogBoundaryTest.php
│   ├── CartAndCheckoutBoundaryTest.php
│   ├── SecurityAndAccessBoundaryTest.php
│   └── SlugAndEncodingBoundaryTest.php
├── Tier3_CrossFeatureCombinations/
│   ├── CatalogToStorefrontCombinationTest.php
│   ├── CartToCheckoutCombinationTest.php
│   ├── CheckoutToAdminOrderLifecycleTest.php
│   └── SettingsToStorefrontCombinationTest.php
└── Tier4_RealWorldScenarios/
    ├── CustomerOrderJourneyScenarioTest.php
    ├── RestaurantManagerDailyOperationsScenarioTest.php
    └── DynamicStorefrontReconfigurationScenarioTest.php
```

### Tier 1: Feature Coverage (Unit & Subsystem Granularity)
*Requirement: ≥5 distinct tests per feature area.*
- **Admin Settings**: Index view, single key updates, batch updates, currency symbol updates, tax rate adjustments.
- **Admin Pages**: List pages, create dynamic page, edit page content, toggle published status, delete page.
- **Admin Navigation**: View navigation manager, create header menu item, create footer menu item, link item to page, reorder items, delete item.
- **Admin Categories**: List categories, create category with image, update category, toggle active status, delete category.
- **Admin Products**: List products, create product with category, update base price, toggle availability, delete product.
- **Admin Product Variants**: Add variant (size/addon), set price adjustment, toggle variant active state, delete variant.
- **Admin Orders**: List orders with pagination, view order details, update status (`new` -> `preparing` -> `ready` -> `completed`), update payment status, delete order.
- **Frontend Home**: 200 OK, dynamic restaurant name rendered from DB, hero section, active categories displayed, header navigation links rendered.
- **Frontend Menu**: 200 OK, category filter tabs, product cards with base prices, variant options rendered, inactive products omitted.
- **Frontend Cart**: View cart, add simple item to session cart, add variant item to session cart, update item quantity, remove item from cart, clear cart.
- **Frontend Checkout**: View checkout page, submit pickup order, submit delivery order with address, order validation, session cart cleared upon submission, redirection to confirmation.

### Tier 2: Boundary & Corner Cases
*Requirement: ≥5 distinct tests per feature/area.*
- **Negative & Zero Prices**: Rejection of negative product prices, zero price acceptance for free items/promotions, negative variant price deductions.
- **Empty Inputs & Max Lengths**: Empty category names, required field validations, extreme string lengths (255+ characters).
- **Security & Access Restrictions**: Unauthenticated access to `/admin/*` redirects to login; standard customer (`is_admin=false`) redirected to `/`; admin (`is_admin=true`) granted full access.
- **Cart & Checkout Boundaries**: Checkout with empty cart rejected, item quantity = 0 or negative rejected, quantity exceeding max (e.g. 99) capped/validated.
- **Order Types & Payment Options**: Validating required delivery address when `order_type=delivery`, optional address when `order_type=pickup`, invalid payment methods rejected.
- **Slugs & Encoding**: Special characters, spaces, accents, and non-ASCII strings in slugs automatically sanitized or validated for uniqueness.

### Tier 3: Cross-Feature Combinations (Pairwise & Pipeline Integration)
*Requirement: End-to-end multi-module data flows.*
1. **Catalog to Storefront Flow**: Admin creates a new Category and associated Products with Variants -> Customer visits `/menu`, filters by the new category -> Customer verifies exact product names, descriptions, and variant price adjustments.
2. **Cart to Checkout Flow**: Customer adds multiple products (simple + variants) with different quantities -> Subtotal, Tax (based on DB `tax_rate`), and Grand Total computed -> Customer proceeds to `/checkout` -> Summary matches calculated amounts.
3. **Checkout to Admin Order Flow**: Customer submits checkout -> Atomic DB transaction inserts `orders` and `order_items` records -> Session cart is purged -> Admin logs in, views order in `/admin/orders`, inspects itemized variants, and transitions status to `completed`.
4. **Settings to Storefront Flow**: Admin updates restaurant name, tax rate, and opening hours in `/admin/settings` -> Frontend layout, header, footer, and cart tax immediately reflect the new values.

### Tier 4: Real-World Scenarios (End-to-End Persona Journeys)
1. **Customer Order Journey**: Guest visits homepage -> navigates to menu -> chooses pizza with "Large (+ $4.00)" and "Extra Cheese (+ $2.00)" -> adds beverage -> modifies cart item quantity -> completes delivery checkout -> receives confirmation receipt with order ID.
2. **Restaurant Manager Daily Operations**: Admin logs in -> reviews dashboard metrics -> visits `/admin/orders` -> filters new orders -> marks order as preparing -> marks as ready -> prints kitchen receipt -> marks as completed.
3. **Dynamic Storefront Reconfiguration**: Restaurant undergoes rebranding -> Admin updates store name, logo, contact phone, creates "Holiday Specials" category with limited-time products -> updates Header navigation menu to link to a new "Holiday Catering" CMS page -> customer storefront verifies all changes live without server restart or hardcoded assets.

---

## 4. Test Execution Guide

### Run All Tests
```bash
php artisan test
```

### Run Only E2E Test Suite
```bash
php artisan test tests/Feature/E2E
```

### Run Specific Tiers
```bash
# Tier 1: Feature Coverage
php artisan test tests/Feature/E2E/Tier1_FeatureCoverage

# Tier 2: Boundary & Corner Cases
php artisan test tests/Feature/E2E/Tier2_BoundaryAndCornerCases

# Tier 3: Cross-Feature Combinations
php artisan test tests/Feature/E2E/Tier3_CrossFeatureCombinations

# Tier 4: Real-World Scenarios
php artisan test tests/Feature/E2E/Tier4_RealWorldScenarios
```

### Run Specific Test Class
```bash
php artisan test tests/Feature/E2E/Tier1_FeatureCoverage/AdminProductsTest.php
```

---

## 5. Interface & Data Contracts for Test Assertions

### Setting Key-Value Map
| Key | Type | Default Test Value | Purpose |
|---|---|---|---|
| `site_name` / `store_name` / `restaurant_name` | `string` | "Bella Napoli Ristorante" | Main branding in header/footer |
| `site_tagline` / `store_tagline` | `string` | "Authentic Wood-Fired Pizza" | Hero & footer tagline |
| `contact_phone` / `store_phone` | `string` | "+1 (555) 234-5678" | Contact info |
| `contact_email` / `store_email` | `string` | "contact@bellanapoli.com" | Contact email |
| `contact_address` / `store_address` | `string` | "123 Culinary Way, Foodville" | Physical address |
| `opening_hours` | `string` | "Mon-Sun: 11:00 AM - 10:00 PM" | Hours info |
| `currency_symbol` | `string` | "$" | Currency prefix |
| `tax_rate` / `tax_rate_percent` | `float`/`string` | "8.25" / "10" | Tax rate percentage |
| `delivery_fee` | `float`/`string` | "5.00" | Standard delivery fee |

### Order Status Enum Matrix
- `order_status`: `['new', 'preparing', 'ready', 'completed', 'cancelled']`
- `payment_status`: `['pending', 'paid', 'failed', 'refunded']`
- `order_type`: `['pickup', 'delivery']`
- `payment_method`: `['cash', 'cod', 'card', 'stripe', 'square', 'online']`
