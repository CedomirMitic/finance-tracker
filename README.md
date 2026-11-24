# FinanceTracker Starter Kit

A ready-to-customize personal finance SaaS built with **Laravel 12**, **Vue 3 + TypeScript** and **Inertia.js**.
You get authentication, a Free / Pro subscription flow with Stripe, background jobs, reports and a demo mode, so you can start with a working product instead of an empty project.

## What is included

- Dashboard, transactions, recurring transactions, statistics, category budgets
- Free plan (15 transactions / month) and Pro plan (budgets, bank import, PDF reports)
- Bank statement import from **CSV and Excel** with column mapping (no PDF import)
- Multi-currency with historical exchange rates (Frankfurter API)
- CSV export (all users) and PDF export (Pro)
- Stripe Checkout, Customer Portal and webhooks through Laravel Cashier
- E-mail verification and welcome e-mail (Resend), Sentry error tracking
- Queue jobs, a nightly scheduler command, a demo mode with nightly reset
- Terms of Service and Privacy Policy pages (**placeholders, see Customization**)

## Requirements

- PHP 8.2+ with the extensions Laravel requires (and `pdo_mysql` or `pdo_pgsql`)
- Composer 2, Node.js 20+ and npm
- MySQL 8+ or PostgreSQL 14+
- A Stripe account (test mode is enough to start), a Resend account (optional), a Sentry project (optional)

## Quick start (local)

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate
```

Edit `.env` (database, see the table below), then:

```bash
php artisan migrate
```

Run these in three terminals:

```bash
php artisan serve         # http://127.0.0.1:8000
npm run dev               # Vite
php artisan queue:work    # required for bank import and currency conversion
```

Restart `queue:work` after you change job code. A running worker keeps the old code in memory (or use `php artisan queue:listen` while developing).

Mail while developing: set `MAIL_MAILER=log` and the e-mails are written to `storage/logs/laravel.log`.

## Environment variables

| Variable | Purpose |
| --- | --- |
| `APP_NAME`, `APP_URL`, `APP_ENV`, `APP_DEBUG` | Application basics. Production: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://...` |
| `DB_CONNECTION` | `mysql` or `pgsql` |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Database credentials |
| `DB_SSLMODE` | Managed PostgreSQL usually needs `require` |
| `SESSION_DRIVER`, `QUEUE_CONNECTION`, `CACHE_STORE` | Default is `database`, which needs no extra service |
| `STRIPE_KEY`, `STRIPE_SECRET` | Stripe API keys |
| `STRIPE_PRICE_ID` | The recurring price the Pro plan uses (`price_...`) |
| `STRIPE_WEBHOOK_SECRET` | Webhook signing secret (`whsec_...`) |
| `MAIL_MAILER`, `RESEND_API_KEY`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` | E-mail delivery |
| `SENTRY_LARAVEL_DSN`, `SENTRY_TRACES_SAMPLE_RATE` | Error tracking. Use a low sample rate in production, for example `0.1` |
| `DEMO_MODE` | `true` enables the demo login and the nightly demo reset. Keep it `false` in production |
| `DEMO_EMAIL`, `DEMO_PASSWORD` | Demo account credentials (optional) |
| `APP_TIMEZONE` | Default `UTC`. Dates in reports follow this setting |

## Stripe setup

1. In the Stripe Dashboard (with **Test mode** on) create a product with a **recurring monthly price** and copy the **Price ID** (`price_...`, not `prod_...`).
2. Put `STRIPE_KEY`, `STRIPE_SECRET` and `STRIPE_PRICE_ID` in `.env`. All three must come from the same account and the same mode.
3. Enable and save the **Customer Portal** in Stripe (Settings, Billing, Customer portal). Without it "Manage subscription" fails.
4. **Webhooks are required.** After checkout the app only learns about the payment through a webhook.
   - Local: `stripe listen --forward-to http://127.0.0.1:8000/stripe/webhook`, then copy the printed `whsec_...` into `STRIPE_WEBHOOK_SECRET`.
   - Production: `php artisan cashier:webhook --url="https://your-domain.com/stripe/webhook"`, then copy that endpoint's signing secret from the Stripe Dashboard into `STRIPE_WEBHOOK_SECRET`.
5. Test with card `4242 4242 4242 4242`, any future expiry date and any CVC.

Cancelling keeps Pro until the paid period ends (grace period). Deleting an account cancels the Stripe subscription and removes the local subscription records.

## Queue worker

Bank imports and currency conversion run as queued jobs. In production run `php artisan queue:work` as a managed process (Supervisor, systemd or a separate service on your platform) and run `php artisan queue:restart` on every deploy.

## Scheduler

Recurring transactions (and the demo reset) run from one command: `php artisan app:nightly`. Pick **one** of these:

- **Classic server:** add a single cron entry
  `* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1`
  The schedule is defined in `routes/console.php`.
- **Platform cron (Railway, Render, ...):** create a cron service that runs `php artisan app:nightly` once a day.

Do not use both, the command would run twice. Check with `php artisan schedule:list`.

## Database notes

MySQL and PostgreSQL are supported. With a managed PostgreSQL set `DB_CONNECTION=pgsql` and `DB_SSLMODE=require`.
Tests use an in-memory SQLite database (`php artisan test`).

## Demo mode

With `DEMO_MODE=true`:
- `/demo-login` signs in the demo account, and new accounts are verified automatically (useful without a mail domain).
- Password / e-mail changes, account deletion and Stripe actions are blocked for the demo account.
- `php artisan demo:reset` restores six months of sample data. `app:nightly` runs it every night.
- `php artisan db:seed --class=DemoSeeder` creates the demo data manually.

Never enable demo mode on a production database that holds real customers. The reset deletes the demo account's data.

## Customization checklist

- [ ] **Name and branding:** `APP_NAME`, the logo and texts in `resources/js/Pages/Welcome.vue` and `resources/js/Layouts/`.
- [ ] **Prices:** the `$7.99` text on the landing page is not read from Stripe. Update it to match your Stripe price.
- [ ] **Free limit:** the 15 transactions / month limit is defined in `app/Providers/AppServiceProvider.php` (`create-transaction`).
- [ ] **Categories:** the category lists are in the create / edit transaction modals.
- [ ] **Terms of Service and Privacy Policy:** `resources/js/Pages/TermsOfService.vue` and `PrivacyPolicy.vue` are **placeholder text, not legal advice**. Replace them with documents that fit your business and jurisdiction (data controller, processors such as Stripe, Resend and Sentry, retention, user rights).
- [ ] **E-mail templates:** `resources/views/emails/`. Verify your sending domain in Resend.
- [ ] **Package metadata:** `composer.json` name, description and license.

## Production checklist

- [ ] `APP_ENV=production`, `APP_DEBUG=false`, HTTPS, a unique `APP_KEY`
- [ ] `php artisan migrate --force`, `npm run build`
- [ ] Optional: `php artisan config:cache route:cache view:cache` (config changes then need a new deploy)
- [ ] Queue worker running, scheduler configured
- [ ] Stripe live keys, live price and a live webhook endpoint
- [ ] `DEMO_MODE=false`
- [ ] Lower `SENTRY_TRACES_SAMPLE_RATE`
- [ ] Trusted proxies: the app trusts all proxies (`bootstrap/app.php`). Restrict this if it is not behind your platform's load balancer.
- [ ] Back up the database

## Troubleshooting

| Symptom | Likely cause |
| --- | --- |
| 500 when clicking Upgrade to Pro | `STRIPE_PRICE_ID` missing, or key and price are from different Stripe modes |
| Paid, but the account is still Free | The Stripe webhook is not configured or `STRIPE_WEBHOOK_SECRET` is wrong |
| "Manage subscription" fails | The Stripe Customer Portal is not enabled and saved |
| Recurring transactions are not created | The scheduler is not running (`app:nightly`) |
| "Processing..." never ends | The queue worker is not running |
| New code has no effect on jobs | Restart the queue worker |
| No e-mails arrive | Verify your sending domain in Resend, or use `MAIL_MAILER=log` locally |
| Currency change or import refused | The exchange rate service is unavailable, or the currency is not supported by Frankfurter |
| Import fails with a column mapping message | The chosen columns do not contain valid amounts or dates |

## Import file notes

- Formats: `.csv`, `.txt`, `.xlsx`, `.xls`, up to 5 MB. The first row must be the header.
- Map the **date**, **description** and **amount** columns and choose the statement currency. One currency per file.
- Negative amounts are expenses, positive amounts are income. Rows without a valid amount are skipped, and if most rows are skipped the whole import is rejected.
- Dates: `2026-08-01` and `01.08.2026` (day.month) are fine, `03/04/2026` is read as month/day. Rows with an unreadable date use the import day.

## Tests

`php artisan test` runs the included authentication and profile tests. Billing, import and reports are not covered by automated tests, so add tests for the parts you change.

## License

Commercial Source License. See the `LICENSE` file for the full terms.

Summary:
- One license covers **one developer** for an unlimited number of end products (personal or client projects).
- You **cannot** resell, sublicense, share, or publish the source code as a starter kit, template, boilerplate, or competing product.
- Updates and support are **not included**; the kit is provided "as is" in the purchased version.

## Support

Per Section 8 of the `LICENSE`, updates and technical support are not included. The kit is delivered "as is" in the version purchased, and you are responsible for customization, deployment, testing, and compliance.

For license or purchase inquiries, contact Cedomir Mitic at `cedomir988@gmail.com`. 
