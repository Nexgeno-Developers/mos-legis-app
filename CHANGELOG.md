# Changelog

## 1.0.0 — MOS Legis (branch `mos-legis`)

Rebuilt the cloned coworking-booking app into MOS Legis on Laravel 13.

### Removed
- Coworking domain (properties, cabins, seats, bookings, customers, companies, visitors, menus, upload manager, invoices).
- Insecure routes: `/command/*` (ran artisan via GET, incl. `key:generate`) and `customers/login-as`.
- Packages not needed by the SOW: debugbar, telescope, jenssegers/agent, maatwebsite/excel, spatie activitylog,
  backup, medialibrary, schedule-monitor, intervention/image, livewire, pest. Bootstrap admin theme.

### Upgraded
- Laravel 11 → 13 (PHP 8.3+), Vite 8, Tailwind 4, PHPUnit 12, spatie/laravel-permission 8.

### Added
- Schema from `schema.sql` as migrations, backed enums, models, factories and seeders (see "Schema changes" below).
- Superadmin console: dashboard, profile, users, roles & permissions, author/content categories, themes, fee matrix,
  submissions (assign, review decisions, stage override, Best Paper, re-check), payments, plagiarism checks, blogs,
  blog categories/tags/comments, job postings, CMS pages with six template editors, enquiries, activity logs, settings.
- Manuscript workflow with auto-assignment, revisions history, certificates, invoices and plagiarism reports (PDF).
- Razorpay checkout + webhook (simulated gateway when keys are absent); India-only tax with frozen billing snapshots.
- Author portal: OTP registration, Google/ORCID sign-in, dashboard, step-form submission with automatic word count,
  revision upload, payments, certificates & invoices, own blogs (approval setting) and jobs, profile and billing address.
- Public site: home, about, editorial board, patrons, policies, submit, archive (+ ZIP), blogs with comments, jobs,
  best paper, contact, careers, plagiarism checker, certificate verification.
- Email/SMS/WhatsApp notification templates for every workflow event; nightly pruning of logs and OTP codes.
- 113 feature tests.

### Schema changes versus `schema.sql` (all flagged in migration comments)
- `manuscript_submissions`: `content_category_theme_id`, `assigned_at`, `stage_changed_at`, `published_at`.
- New `manuscript_revisions` table (reviewer decisions, remarks, resubmitted files).
- `payments`: `invoice_number`, `gateway_order_id`; `payment_method` nullable.
- `otp_verifications.otp_code` widened for a hash; `attempts` column.
- `author_profiles.author_category_id` and `enquiries.phone` nullable.
- `manuscript_content_category_themes.period` is a DATE (first of month).
- `blogs.status` adds `Pending` (author-approval setting).
- Settings: `general.blog_author_approval_required`.
- Kept from the old app: `notification_templates`, `notification_logs`.
- Foreign-key/index names follow Laravel defaults instead of the `fk_*` names in the SQL file.
