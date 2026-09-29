# MOS Legis

Journal and manuscript management platform built from the MOS Legis Scope of Work:
a superadmin console (SOW section A), an author portal (B) and the public website (C).

**Stack:** Laravel 13 · PHP 8.3+ · MySQL 8 (MariaDB 10.4+ works) · Blade + Alpine.js + Tailwind CSS 4 · Vite.

## Requirements

- PHP 8.3+ with `pdo_mysql`, `mbstring`, `zip`, `intl`, `fileinfo`, `openssl`, and **`gd`**
  (dompdf needs GD to embed the logo in certificates and invoices; without GD the PDFs render without the logo).
- Composer 2, Node 20+, MySQL 8 / MariaDB.

## Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Edit `.env`:

| Key | Notes |
| --- | --- |
| `APP_URL`, `APP_TIMEZONE` | e.g. `http://localhost:8010`, `Asia/Kolkata` |
| `DB_CONNECTION`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | use `mariadb` as the connection for MariaDB |
| `MAIL_*` | SMTP for OTP codes, password resets and workflow emails |
| `SUPERADMIN_EMAIL`, `SUPERADMIN_PASSWORD` | first admin account created by the seeder |
| `RAZORPAY_KEY_ID`, `RAZORPAY_KEY_SECRET`, `RAZORPAY_WEBHOOK_SECRET` | leave blank locally to use the **simulated gateway** |
| `GOOGLE_*`, `ORCID_*` | social sign-in; buttons report "not configured" until set |
| `PLAGIARISM_DRIVER` | `fake` until a vendor is chosen (`PLAGIARISM_FAKE_SIMILARITY` forces a score) |
| `SMS_PROVIDER`, `WHATSAPP_PROVIDER`, `EMAIL_PROVIDER` | `log` / `mail` by default; `smsgatewayhub`, `twilio`, `wati`, `brevo` available |

Then:

```bash
php artisan migrate --seed      # roles, permissions, settings, superadmin, categories, CMS pages, notification templates
php artisan storage:link
npm run build                   # or: npm run dev
```

In `local`, the seeder also adds demo data. Demo logins (password `password`):
`admin@moslegis.com` (superadmin, at `/admin`), `reviewer@moslegis.test` (reviewer), `author@moslegis.test` (author, at `/login`).

## Running

```bash
php artisan serve               # web
php artisan queue:work          # notifications, plagiarism checks (QUEUE_CONNECTION=database)
php artisan schedule:work       # nightly: activity logs older than 30 days, expired OTP codes
```

In production run the queue worker under a supervisor and add the scheduler cron:
`* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1`.
Point the Razorpay webhook at `POST /payments/razorpay/webhook` (events `payment.captured`, `payment.failed`).

## Tests

Tests run against the `mos_legis_test` database (see `phpunit.xml`):

```bash
php artisan test
```

## Architecture notes

- **Roles** (Spatie): `superadmin` (every permission via `Gate::before` on permission names), `reviewer`
  (permissions granted from Roles & Permissions; sees only assigned manuscripts unless given `submissions.view-all`),
  `author` (website only). Custom editorial roles can be added. Permission catalogue: `app/Support/Permissions.php`.
- **Manuscript pipeline:** `app/Services/Manuscripts/ManuscriptWorkflow.php` (stage rules, notifications),
  `ReviewerAllocator` (lowest workload, then longest since last assignment), `FeeCalculator` (matrix + India-only tax).
- **Payments:** `PaymentGateway` interface with `RazorpayGateway` / `SimulatedGateway`; `PaymentService` settles
  exactly once (row lock) whether the browser callback or the webhook arrives first; each payment freezes its billing snapshot.
- **Plagiarism:** implement `App\Services\Plagiarism\PlagiarismChecker` for the chosen vendor and register it in
  `AppServiceProvider` under `PLAGIARISM_DRIVER`.
- **Settings:** `app/Support/SettingsRegistry.php` defines every setting; read with `settings('group.key')` (cached).
- **Notifications:** editable templates in `notification_templates` (email/SMS/WhatsApp), sent through
  `App\Notifications\WorkflowNotifier` (queued after commit).
- **Files:** manuscripts, certificates, reports and CVs are on the private `local` disk and streamed through
  authorised controllers; images are on the `public` disk.
