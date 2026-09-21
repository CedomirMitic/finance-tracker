# 💰 Personal Finance Manager

A modern, full-stack SaaS financial management application built with a focus on performance, type safety, security, data visualization, and seamless transactional workflows. This project demonstrates a robust "Modern Monolith" architecture by seamlessly connecting a production-ready Laravel backend with a reactive Vue 3 frontend.

---

## 🚀 Live Demo

* **URL:** https://finance-tracker-cedomir.up.railway.app/
* **Test Credentials:**
  * **Email:** `test@example.com`
  * **Password:** `test123123`

---

## 🚀 Key Features

* **📊 Interactive Analytics:** High-level overview of Total Income, Total Expenses, and Net Balance powered by ApexCharts.
* **📉 Smart Budgeting:** Set monthly limits for specific categories with real-time visual progress indicators.
* **⏳ Automatic Subscriptions:** Automate recurring transactions like rent or subscriptions with flexible billing schedule management.
* **🌍 Multi-Currency Support & Live Rates:** Seamlessly switch user currency in profile settings with automatic live conversion and rate updates powered by the Frankfurter API across the entire UI.
* **📂 Smart PDF & CSV Bank Import:** Advanced file parser allowing users to upload any bank statement (PDF or CSV), dynamically map columns (Date, Amount, Description, Currency), and bulk-import transactions effortlessly.
* **📧 Transactional Emails via Resend:** Reliable, modern email delivery for user verification flows, welcome messages, and account notifications powered by Resend *(Currently in Test mode)*.
* **🔍 Transaction Drill-down:** Click on any category to instantly filter and inspect the specific transactions contributing to that total.
* **📄 Advanced Data Export:** Generate professional PDF reports (Pro tier) or CSV files with instant client-side feedback and auto-dismissing notifications.
* **💳 SaaS Billing & Subscriptions:** Fully integrated Stripe Checkout and Customer Portal supporting Free tier limits, Pro upgrades, and complete subscription lifecycle management via Laravel Cashier *(Currently configured with Stripe Test mode keys)*.
* **🔒 Production Ready & Error Tracking:** Comprehensive backend error tracking and monitoring using Sentry, secure background job processing, and legal compliance pages (ToS & Privacy Policy).
* **📱 Responsive UI:** Fully optimized for both desktop and mobile views using Tailwind CSS.

---

## 🛠 Tech Stack

* **Backend:** Laravel 12 (PHP)
* **Frontend:** Vue 3 (Composition API) with TypeScript
* **Bridge:** Inertia.js (Seamless SPA experience)
* **Email Service:** Resend (Transactional emails)
* **Styling:** Tailwind CSS
* **Build Tool:** Vite
* **Database & Billing:** MySQL (Aiven) & Stripe (Laravel Cashier)
* **Key Libraries & Services:**
  * `barryvdh/laravel-dompdf` (PDF Generation)
  * `vue3-apexcharts` (Data Visualization)
  * `ziggy-js` (Named Laravel routes in TypeScript)
  * `Sentry` (Backend Error Monitoring)
  * `Frankfurter API` (Foreign Exchange & Multi-currency rates)

---

## 🌐 Production Deployment & Configuration Notes

* **Infrastructure:** Hosted on Railway using standard container deployment, connected to an external Aiven MySQL database.
* **External Services (Test Mode):** Both Stripe and Resend are currently set up using Test credentials for seamless portfolio demonstration. *(Note: Resend in test mode delivers emails only to verified addresses until a custom domain is configured).*
* **Security:** Forced HTTPS scheme in production via AppServiceProvider to ensure secure asset loading and seamless Stripe webhook processing.
* **Background Processing & Queues:** Dedicated queue workers handling currency synchronization jobs and heavy bank import parsing tasks asynchronously.

---

📦 Installation & Setup

1. Clone the repository:
```bash
git clone [https://github.com/CedomirMitic/finance-tracker.git](https://github.com/CedomirMitic/finance-tracker.git)
cd finance-tracker

2. Install dependencies:
composer install
npm install

3. Environment configuration:
cp .env.example .env
php artisan key:generate

4. Run migrations:
php artisan migrate

5. Compile Assets:
npm run build

6. Launch for Development:
# Terminal 1: Laravel Server
php artisan serve

# Terminal 2: Vite Hot Module Replacement (HMR)
npm run dev

# Terminal 3: Laravel Queue Worker (For background jobs and imports)
php artisan queue:work