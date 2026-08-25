## 2026-08-25T05:18:00Z
You are the Forensic Auditor conducting an independent integrity audit of the Laravel 11 Restaurant Platform.

Working Directory: i:\Client Restaurant\.agents\auditor_forensic\
Original Request: i:\Client Restaurant\.agents\ORIGINAL_REQUEST.md
Project Blueprint: i:\Client Restaurant\PROJECT.md
Test Readiness: i:\Client Restaurant\TEST_READY.md

Your Task:
1. Read `i:\Client Restaurant\.agents\ORIGINAL_REQUEST.md` and `i:\Client Restaurant\PROJECT.md`.
2. Perform comprehensive static analysis and forensic audit of all implemented files:
   - `app/Models/*.php`
   - `app/Http/Controllers/**/*.php`
   - `app/Providers/AppServiceProvider.php`
   - `resources/views/**/*.blade.php`
   - `database/factories/*.php`
   - `database/seeders/DatabaseSeeder.php`
   - `routes/web.php`
   - `tests/Feature/**/*.php`
3. Check for integrity violations:
   - Are there any fake stubs, bypasses, or dummy implementations?
   - Are there any hardcoded test results or mock shortcuts returning static data instead of querying Eloquent models?
   - Are all database tables, columns, relations, and transactions genuine?
   - Does the frontend contain any hardcoded store names, phone numbers, or navigation links violating R1/R2?
   - Is zero-terminal compliance (R3) properly maintained?
4. Run the full test suite (`php artisan test`) and verify genuine test execution.
5. Write your complete forensic audit report to `i:\Client Restaurant\.agents\auditor_forensic\audit.md` and handoff report to `i:\Client Restaurant\.agents\auditor_forensic\handoff.md`.
6. Issue your verdict (CLEAN or INTEGRITY VIOLATION) and send a message to parent when complete.
