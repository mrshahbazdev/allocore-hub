# KPI Tool

A professional **Key Performance Indicator (KPI) Management System** built with Laravel, Vue.js, and Inertia.js. Track, visualize, and analyze business KPIs with an Excel-style spreadsheet, interactive charts, and a bilingual interface (German/English).

---

## Features

- **Authentication** — Secure login, registration, and profile management (Laravel Breeze)
- **Dashboard** — Summary cards, status doughnut chart, monthly bar chart, top KPIs, recent entries
- **KPI Spreadsheet** — Excel-style monthly grid with inline editing for Actuals & Targets, auto-calculated Difference and % Deviation
- **KPI Definitions** — Full CRUD with bilingual support (DE/EN), formula, unit, category, thresholds
- **KPI Catalog** — 35+ pre-built templates across 6 categories (Strategic, Sales, Operations, Marketing, Financial, HR)
- **Target Generator** — Auto-generate monthly targets with custom growth rate
- **Chart.js Visualization** — Trend line charts on KPI detail pages with color-coded status points
- **CSV Import/Export** — Export spreadsheet as CSV; bulk-import values per KPI
- **Dark Mode** — Toggle with localStorage persistence
- **Bilingual UI** — German (primary) and English via vue-i18n locale switcher
- **Shared Hosting Ready** — `.htaccess` included, pre-built assets committed

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 13 (PHP) |
| Frontend | Vue 3, Inertia.js |
| Styling | Tailwind CSS v4 |
| Charts | Chart.js + vue-chartjs |
| Auth | Laravel Breeze |
| Database | SQLite (default) / MySQL |
| Build | Vite |
| i18n | vue-i18n |

---

## Installation

### Requirements
- PHP 8.2+
- Composer
- Node.js 18+ & npm (for development only)

### Setup

```bash
git clone https://github.com/mrshahbazdev/kpi-tool.git
cd kpi-tool

# Install dependencies
composer install
npm install

# Environment
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate --seed

# Build frontend
npm run build

# Start server
php artisan serve
```

Visit `http://localhost:8000` — Login with **admin@kpi-tool.com** / **password**

---

## Shared Hosting Deployment

1. Upload the entire project to your hosting root
2. Point the domain's document root to the `public/` folder (or let the root `.htaccess` redirect)
3. Run on server:
   ```bash
   composer install --no-dev --optimize-autoloader
   cp .env.example .env
   php artisan key:generate
   ```
4. Edit `.env`:
   ```
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com
   ```
5. Run migrations:
   ```bash
   php artisan migrate --seed
   ```

> Build assets are already committed in `public/build/` — no npm required on the server.

---

## Project Structure

```
app/
├── Http/Controllers/
│   ├── DashboardController.php      # Dashboard with stats & charts
│   ├── KpiDefinitionController.php  # KPI CRUD
│   ├── KpiSpreadsheetController.php # Spreadsheet + CSV export
│   ├── KpiValueController.php       # Value entry + CSV import
│   └── LocaleController.php         # Language switcher
├── Models/
│   ├── KpiDefinition.php            # KPI metadata
│   ├── KpiValue.php                 # Time-series values
│   └── KpiMonthlyTarget.php         # Monthly targets
resources/js/
├── Pages/
│   ├── Dashboard.vue                # Main dashboard
│   ├── Kpis/
│   │   ├── Spreadsheet.vue          # Excel-style grid
│   │   ├── Index.vue                # KPI list
│   │   ├── Show.vue                 # Detail + chart + value entry
│   │   ├── Create.vue / Edit.vue    # Forms
│   │   └── Catalog.vue              # Template browser
│   └── Auth/                        # Login, Register, etc.
├── Components/Layout/
│   └── AppShell.vue                 # Sidebar, dark mode, locale
└── i18n/                            # DE/EN translations
```

---

## Database Tables

| Table | Purpose |
|-------|---------|
| `users` | Authentication |
| `kpi_definitions` | KPI metadata (name, formula, unit, thresholds, category) |
| `kpi_values` | Recorded values with status (on_target / warning / critical) |
| `kpi_monthly_targets` | Monthly target values with growth rate |

---

## KPI Status Logic

| Status | Higher is Better | Lower is Better |
|--------|-----------------|-----------------|
| On Target | value > warning_threshold | value < warning_threshold |
| Warning | critical < value <= warning | warning <= value < critical |
| Critical | value <= critical_threshold | value >= critical_threshold |

---

## Default Login

| Field | Value |
|-------|-------|
| Email | admin@kpi-tool.com |
| Password | password |

---

## License

MIT
