# Allocore Hub

The **Allocore Control Tower** — a central hub that aggregates KPIs from the whole Allocore tool ecosystem (AuditPro, InvoiceMaker, EasySOP, …) into one company-scoped dashboard. Built with Laravel, Vue 3, and Inertia.js. Bilingual (German/English).

Each spoke tool authenticates with an `X-Allocore-Api-Key` and pushes metrics to `POST /api/allocore/kpi/ingest`. The hub auto-creates "connected" KPIs from those metrics, while entrepreneurs set targets (debit figures) and thresholds and control who sees what.

**Phase 1 (current):** AuditPro → Hub. One audit run pushes 6 metrics — *Enterprise Readiness* plus the 5 pillars (Umsatz, Gewinn, Ordnung, Einfluss, Vermächtnis).

---

## Features

### Control Tower / multi-tenancy
- **Companies** — every registration creates a company; the registrant becomes its **owner**
- **Roles** — `owner` / `manager` (full access) and `member` (sees only assigned KPIs)
- **Tools page** — connect/disconnect Allocore tools, reveal & regenerate API keys, enable/disable
- **Team page** — create users, set roles, assign which KPIs each member can see
- **Allocore ingestion API** — `POST /api/allocore/kpi/ingest`, idempotent on `external_ref + metric_key`
- **Connected KPIs** — auto-created from incoming metrics via a bilingual metadata catalog

### KPI management
- **Dashboard** — Enterprise Readiness hero + audit pillars, status doughnut, monthly bar chart, top KPIs, recent entries (all company-scoped)
- **KPI Spreadsheet** — Excel-style monthly grid with inline Actuals & Targets, auto Difference and % Deviation
- **KPI Definitions** — Full CRUD (DE/EN), formula, unit, category, target/warning/critical thresholds
- **KPI Catalog** — 35+ pre-built templates across 6 categories
- **Target Generator** — Auto-generate monthly targets with a growth rate
- **CSV Import/Export**, **Dark Mode**, **Bilingual UI**, **Shared-hosting ready**

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
git clone https://github.com/mrshahbazdev/allocore-hub.git
cd allocore-hub

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

Visit `http://localhost:8000` and **register** — your first account creates a company and becomes its owner. Then open **Tools**, connect **AuditPro**, and copy the generated API key into the audit tool.

### Allocore integration

| Setting | Value |
|---------|-------|
| Ingest endpoint | `POST /api/allocore/kpi/ingest` |
| Auth header | `X-Allocore-Api-Key: alc_…` |
| Hub URL config | `ALLOCORE_HUB_URL` (falls back to `APP_URL`) |

Example payload:

```json
{
  "source": "audit",
  "external_ref": "audit-run-123",
  "recorded_at": "2026-07-09",
  "metrics": [
    { "key": "enterprise_readiness", "value": 3.4, "scale_max": 5 },
    { "key": "audit.umsatz", "value": 4, "scale_max": 5 }
  ]
}
```

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
| `companies` | Tenants (name, slug, plan) |
| `users` | Authentication + `company_id` + `role` |
| `tool_accesses` | Per-company connected tools + API keys + sync status |
| `kpi_definitions` | KPI metadata + `company_id`, `source`, `source_key`, `is_connected`, `scale_max` |
| `kpi_values` | Recorded values + `source`, `external_ref` (idempotency) |
| `kpi_monthly_targets` | Monthly target values with growth rate |
| `kpi_user_assignments` | Per-member KPI visibility (with `can_edit`) |

---

## KPI Status Logic

| Status | Higher is Better | Lower is Better |
|--------|-----------------|-----------------|
| On Target | value > warning_threshold | value < warning_threshold |
| Warning | critical < value <= warning | warning <= value < critical |
| Critical | value <= critical_threshold | value >= critical_threshold |

---

## License

MIT
