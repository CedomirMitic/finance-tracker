# 💰 Personal Finance Manager

A modern, full-stack financial management application built with a focus on performance, type safety, and data visualization. This project demonstrates a "Modern Monolith" architecture by seamlessly connecting a robust Laravel backend with a reactive Vue 3 frontend.

## 🚀 Live Demo
**URL:** [https://finance-tracker-cedomir.up.railway.app/](https://finance-tracker-cedomir.up.railway.app/)

**Test Credentials:**
- **Email:** `test@example.com`
- **Password:** `test123123`

## 🚀 Key Features

* **📊 Interactive Analytics:** High-level overview of Total Income, Total Expenses, and Net Balance using **ApexCharts**.
* **📉 Smart Budgeting:** Set monthly limits for specific categories. Visual progress bars turn red when you exceed your budget.
* **⏳ Automatic Subscriptions:** Automate recurring transactions like rent or subscriptions. The backend handles periodic entries without manual input.
* **🔍 Transaction Drill-down:** Click on any category to instantly filter and view the specific transactions contributing to that total.
* **📄 Data Export:** Generate professional **PDF reports** or **CSV files** for external analysis in Excel/Google Sheets.
* **📱 Responsive UI:** Fully optimized for both desktop and mobile views using **Tailwind CSS**.


## 🛠 Tech Stack

* **Backend:** Laravel 12.52.0 (PHP)
* **Frontend:** Vue 3 (Composition API) with TypeScript
* **Bridge:** Inertia.js (Seamless SPA experience)
* **Styling:** Tailwind CSS
* **Build Tool:** Vite 6.4 (Latest major version)
* **Database:** MySQL (Hosted on Aiven for production)
* **Key Libraries:**
    * `barryvdh/laravel-dompdf` (PDF Generation)
    * `vue3-apexcharts` (Data Visualization)


## 🌐 Production Deployment

This application is optimized for **Railway** and **Aiven**.

- **Environment Sync:** Uses `VITE_APP_NAME="${APP_NAME}"` to ensure consistent branding across PHP and JavaScript.
- **Security:** Forced HTTPS scheme in production via `AppServiceProvider` to ensure secure asset loading.
- **CI/CD:** Automatic builds using Railway's Nixpacks, triggered on every `git push`.
- **Database:** Remote MySQL integration with SSL for secure cloud data management.
## 📦 Installation & Setup

1.  **Clone the repository:**
    ```bash
    git clone [https://github.com/CedomirMitic/finance-tracker.git]
    cd finance-tracker
    ```

2.  **Install dependencies:**
    ```bash
    composer install
    npm install
    ```

3.  **Environment configuration:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Run migrations:**
    ```bash
    php artisan migrate
    ```

5. **Compile Assets:**
    ```bash
    npm run build
    ```

6.  **Launch for Development**
    ```bash
    # Terminal 1: Laravel Server
    php artisan serve
    
    # Terminal 2: Vite Hot Module Replacement (HMR)
    npm run dev
    ```
    
**Build with ❤️ using Laravel & Vue.**