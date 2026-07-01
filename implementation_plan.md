# Detailed Implementation Plan for DukanHisab Multi‑Shop SaaS ERP

## Goal Recap
Build a production‑ready Laravel 12 application with:
- Blade + Tailwind CSS front‑end (Option A).
- MySQL database.
- Stubbed subscription billing.
- Dark‑mode toggle via user preference.
- PDF & barcode generation.
- Database queue driver.
- Email + Firebase push notifications (WhatsApp later).
- Docker + GitHub Actions CI/CD.
- Multi‑language UI (localization).

---

## Remaining Decisions Required
> [!WARNING]
> The following items are still unanswered. Please provide your preferences so we can finalize the scaffolding.

1. **Social login** – Google/Facebook login needed? (yes/no)
2. **Staff role permissions** – List granular permissions (e.g., `sales.read`, `inventory.manage`).
3. **Premium feature list** – Which features to lock behind Premium (e.g., PDF download, WhatsApp share, cloud backup).
4. **Audit‑log retention** – Duration to keep logs (e.g., 90 days, 1 year).
5. **Multi‑currency support** – Required? (yes/no)
6. **File storage** – Local filesystem or S3?
7. **Backup strategy** – Schedule (daily/weekly) and retention count.

---

## Proposed Step‑by‑Step Execution
### 1️⃣ Project Bootstrap
- Run `npx -y create-laravel-app@latest ./` (Laravel 12).
- Install Composer packages:
  - `laravel/sanctum`
  - `spatie/laravel-permission`
  - `barryvdh/laravel-dompdf`
  - `milon/barcode`
  - `spatie/laravel-activitylog`
- Install NPM packages:
  - `tailwindcss`
  - `alpinejs`
  - `chart.js`
  - `vite`

### 2️⃣ Environment Setup
- Create `.env` with MySQL credentials, `APP_URL`, `APP_ENV=local`.
- Add `SANCTUM_STATEFUL_DOMAINS` and `SESSION_DOMAIN`.
- Configure `QUEUE_CONNECTION=database`.
- Add `FIREBASE_API_KEY` placeholder.

### 3️⃣ Database & Migrations
- Generate migrations for all core tables (users, shops, products, categories, units, sales, purchases, customers, suppliers, transactions, expenses, cash_books, bank_accounts, subscriptions, payments, notifications, settings, audit_logs, etc.).
- Add `shop_id` foreign key to enable multi‑tenant scoping.
- Add `is_app` and `session_type` columns to `institutes` (for device tracking).
- Add `locale` column to `users` for language preference.

### 4️⃣ Core Architecture
- Create `ShopScopeMiddleware` (already present) and register alias `shop.scope`.
- Configure guards in `config/auth.php`:
  - `sanctum` guard for API.
  - `web` guard for Blade routes.
- Set up Spatie roles/permissions (`owner`, `staff`, `admin`, `super_admin`).
- Create `HasShopScope` trait for models.

### 5️⃣ Models & Factories
- Generate models with `HasFactory`, `SoftDeletes`, `Activitylog` traits.
- Define relationships (e.g., `Shop hasMany Products`).
- Create factories for seeding demo data.

### 6️⃣ Policies & Gates
- Generate policies for each primary model based on roles.
- Register policies in `AuthServiceProvider`.

### 7️⃣ API Controllers (Resource Controllers)
- AuthController (login, logout, token issuance).
- ShopController, ProductController, CategoryController, UnitController, SaleController, PurchaseController, CustomerController, SupplierController, CashBookController, BankAccountController, ExpenseController, TransactionController, ReportController, PremiumController, etc.
- Implement localization: return translated strings via `__('key')`.

### 8️⃣ Routes
- `routes/api.php` – protected by `auth:sanctum` and `shop.scope`.
- `routes/web.php` – Blade SPA entry point with wildcard for Vue/React fallback (but we use Blade).
- `routes/admin.php` – Super Admin routes with `admin` guard.

### 9️⃣ Front‑end (Blade + Tailwind)
- Create base layout `resources/views/layouts/app.blade.php` with dark‑mode support (CSS class toggled via `prefers-color-scheme` or user setting stored in DB).
- Create navigation, sidebar, top‑bar components.
- Use Tailwind UI for cards, tables, modals.
- Add language selector dropdown (stores `locale` in session and updates user record).
- Integrate Axios for API calls and Chart.js for charts.

### 🔟 Localization (i18n)
- Install `spatie/laravel-translation-loader` (or Laravel built‑in JSON localization).
- Create `resources/lang/en/*.json` and `resources/lang/es/*.json` (example languages).
- Add middleware to set app locale from authenticated user `locale` field.
- Wrap all UI strings with `__('…')`.

### 1️⃣1️⃣ PDF & Barcode
- Create Blade view for invoice PDF.
- Implement `InvoiceController::downloadPdf($id)` using `DomPDF`.
- Add barcode generation in `ProductController` using `milon/barcode`.

### 1️⃣2️⃣ Notifications
- Configure mail driver (SMTP placeholder).
- Create `FirebaseNotification` service class (placeholder methods).
- Queue notification jobs.

### 1️⃣3️⃣ Queue & Scheduler
- Run `php artisan queue:table && php artisan migrate`.
- Set up scheduler tasks in `app/Console/Kernel.php` (reminders, subscription expiry, backups).

### 1️⃣4️⃣ Docker & CI/CD
- Write `Dockerfile` (PHP 8.3, extensions, Composer, Node).
- Write `docker-compose.yml` (php, nginx, mysql, redis optional).
- Create GitHub Actions workflow:
  - `phpstan` lint
  - `phpunit` tests
  - `npm run build`
  - Build Docker image and push to registry.

### 1️⃣5️⃣ Testing
- Write PHPUnit feature tests for authentication, shop scoping, CRUD, permission enforcement.
- Write unit tests for model scopes.
- Write integration tests for subscription stub flow.

### 1️⃣6️⃣ Final Touches
- Add dark‑mode toggle UI component.
- Add language switcher.
- Ensure all routes are named and documented.
- Add API documentation (e.g., using `scribe`).

---

## Verification Plan
**Automated**
- `php artisan test` after each major feature.
- Lint with `phpstan`.
- End‑to‑end API tests via `pest` or Postman collection.

**Manual**
- Spin up Docker containers, register a shop, switch languages, toggle dark mode, generate PDF, send a Firebase push (placeholder), verify queue workers.
- Verify Super Admin can manage shops and users.

---

## Next Steps
1. **Provide answers** to the remaining decisions listed above.
2. Once clarified, I will generate a `task.md` checklist and begin execution.

*Prepared by Antigravity – your AI architect.*
