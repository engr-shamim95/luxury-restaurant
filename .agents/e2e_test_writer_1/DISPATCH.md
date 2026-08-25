## 2026-08-25T04:33:48Z

You are the E2E Test Writer responsible for the E2E Testing Track of the Laravel 11 Restaurant Platform.

Working Directory: i:\Client Restaurant\.agents\e2e_test_writer_1\
Original Request: i:\Client Restaurant\.agents\ORIGINAL_REQUEST.md
Project Blueprint: i:\Client Restaurant\PROJECT.md

Your Task:
1. Read `i:\Client Restaurant\.agents\ORIGINAL_REQUEST.md` and `i:\Client Restaurant\PROJECT.md`.
2. Inspect the Feature Inventory in `PROJECT.md` and the survey reports:
   - `i:\Client Restaurant\.agents\explorer_survey_testing\analysis.md`
   - `i:\Client Restaurant\.agents\explorer_survey_frontend\analysis.md`
   - `i:\Client Restaurant\.agents\explorer_survey_backend\analysis.md`
3. Design and create the opaque-box test infrastructure and comprehensive 4-Tier test suite:
   - Create `TEST_INFRA.md` at project root (`i:\Client Restaurant\TEST_INFRA.md`).
   - Create comprehensive PHPUnit feature test classes in `tests/Feature/E2E/` (or `tests/Feature/`):
     * Tier 1: Feature Coverage (≥5 tests per feature: Admin Settings, Pages, Navigation, Categories, Products, Product Variants, Orders, Frontend Home, Menu, Cart, Checkout).
     * Tier 2: Boundary & Corner Cases (≥5 per feature: negative/zero prices, empty inputs, unauthenticated/non-admin access restrictions, empty cart checkout, invalid order types, special character slugs).
     * Tier 3: Cross-Feature Combinations (Pairwise flows: create category & product -> display in menu -> add variant to cart -> checkout -> verify order in admin).
     * Tier 4: Real-World Scenarios (Full customer & manager workflows, dynamic settings changes reflected on frontend and cart tax calculations).
   - Verify tests run with `php artisan test` or SQLite in-memory runner.
   - Once all test files are written and validated, create `TEST_READY.md` at `i:\Client Restaurant\TEST_READY.md` summarizing total test counts and tier coverage checklist.
4. Write your handoff report to `i:\Client Restaurant\.agents\e2e_test_writer_1\handoff.md` and send a message to parent when complete.

File Ownership:
- Exclusively owns: `i:\Client Restaurant\TEST_INFRA.md`, `i:\Client Restaurant\TEST_READY.md`, and test files under `tests/Feature/E2E/`.
- Do NOT edit implementation source code in `app/` or `resources/views/`.
