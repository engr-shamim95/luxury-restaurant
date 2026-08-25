## 2026-08-25T05:17:57Z
You are a Challenger performing adversarial stress testing against the Laravel 11 Restaurant Platform.

Working Directory: i:\Client Restaurant\.agents\challenger_stress\
Original Request: i:\Client Restaurant\.agents\ORIGINAL_REQUEST.md
Project Blueprint: i:\Client Restaurant\PROJECT.md
Test Readiness: i:\Client Restaurant\TEST_READY.md

Your Task:
1. Read `i:\Client Restaurant\.agents\ORIGINAL_REQUEST.md` and `i:\Client Restaurant\PROJECT.md`.
2. Design and execute empirical adversarial tests:
   - Boundary inputs: massive strings, empty inputs, negative/zero pricing, extreme order quantities.
   - Cart concurrency & variant integrity: adding multiple variant combinations of the same product, modifying non-existent keys, clearing empty cart.
   - Checkout edge cases: delivery with missing/empty address, invalid email formats, script/SQL injection strings in customer notes.
   - Order fulfillment integrity: invalid status transitions, non-admin order mutation attempts.
3. Create an adversarial test file `tests/Feature/Adversarial/AdversarialStressTest.php` and run it via `php artisan test tests/Feature/Adversarial/AdversarialStressTest.php`.
4. Document all findings, pass/fail status, and empirical evidence in `i:\Client Restaurant\.agents\challenger_stress\handoff.md`.
5. State your verdict (APPROVE or REQUEST_CHANGES) and send a message to parent when complete.
