## 2026-08-25T04:23:26Z
You are an Explorer agent surveying the Backend Architecture and Admin requirements of this Laravel 11 restaurant platform.

Working Directory: i:\Client Restaurant\.agents\explorer_survey_backend\
Original Request: i:\Client Restaurant\.agents\ORIGINAL_REQUEST.md

Your task:
1. Read `i:\Client Restaurant\.agents\ORIGINAL_REQUEST.md`.
2. Inspect the codebase at `i:\Client Restaurant`:
   - Database schema, migrations, seeders, and Eloquent models (Settings, Pages, Navigation, Categories, Products, Product Variants, Orders, Users, etc.).
   - Existing routes (`routes/web.php`, `routes/admin.php`, `routes/api.php`, etc.) and route groups / middleware (`admin` middleware group, auth, etc.).
   - Existing controllers (`app/Http/Controllers/...`, `app/Http/Controllers/Admin/...`).
   - Check what Admin CRUDs already exist, what is partially implemented, and what is missing.
   - Enumerate all required fields, relationships, validations, image/file uploads, and business rules for:
     * Settings (key-value or structured, site title, logo, contact, social links, opening hours, etc.)
     * Pages (title, slug, content, is_published, etc.)
     * Navigation / Menus (labels, URLs/routes, parent/child, order, locations)
     * Categories (name, slug, description, image, parent, sort order, status)
     * Products (category_id, name, slug, description, base price, image, is_active, featured)
     * Product Variants (product_id, name/type, price modifier / variant price, SKU, stock/status)
     * Orders (order_number, customer details, total, status, items, variant selections, payment status)
3. Write a comprehensive survey report to `i:\Client Restaurant\.agents\explorer_survey_backend\analysis.md` and a handoff to `i:\Client Restaurant\.agents\explorer_survey_backend\handoff.md`.
4. Send a message back to parent when complete referencing the file paths.
