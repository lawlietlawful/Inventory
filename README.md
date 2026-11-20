# 🍽️ Canteen Management System (POS)

An enterprise-grade, school-compliant Point of Sale (POS) and Canteen Management System built with **Laravel 11**, **PostgreSQL 18**, **Livewire 3**, **Bootstrap 5.3 CDN**, and **Chart.js CDN**. Designed with an executive minimalist interface, robust database concurrency protection, and seamless multi-schema integration.

---

## 📌 System Highlights & Technical Features

- **Multi-Schema Database Architecture:**
  - Integrates seamlessly with a shared student authentication table (`app1.users`) without running migrations or modifying table definitions.
  - Canteen operations are strictly isolated within the `canteen_pos` PostgreSQL schema.
- **Transactional Concurrency & Data Integrity:**
  - Pessimistic row-locking (`lockForUpdate()`) during checkout to prevent inventory race conditions or negative stock overselling.
  - Complete multi-step checkout (`orders`, `order_items`, `payments`, stock deduction) encapsulated in a single atomic `DB::transaction()`.
  - Database-level generated column compliance: PostgreSQL handles `order_items.line_total` generated column without framework interference.
- **Interactive Livewire 3 Point of Sale:**
  - Instant live cart reactivity with zero full-page reloads.
  - Dual payment channels (Cash with live change calculation and GCash with dynamic scan QR modal).
  - Cashier keyboard shortcuts (`F2` search, `F4` payment toggle, `Enter` checkout, `Esc` dismiss).
  - Audio feedback using Web Audio API synthesis (beep on item scan, chime on checkout).
  - 80mm thermal receipt iframe printing with instant verification QR code.
- **Order Cancellation & Stock Restoration:**
  - Complete order voiding workflow with stock quantities restored back into inventory atomically.
  - Automatic re-enablement of product availability when stock is replenished.
- **Executive Minimalist UI:**
  - Bespoke obsidian slate palette (`#0f172a`), Plus Jakarta Sans and Inter typography, tabular financial figures (`tabular-nums`), and custom vector SVG brand marks.

---

## 🛠️ Technology Stack

| Layer | Technology | Details |
| :--- | :--- | :--- |
| **Backend Framework** | Laravel 11.x | PHP 8.2+ / PHP 8.4 compatible |
| **Database** | PostgreSQL 18 | Multi-schema (`app1` and `canteen_pos`) |
| **Reactive POS** | Livewire 3 | Real-time frontend state synchronization |
| **CSS / UI Styling** | Bootstrap 5.3 CDN | Custom obsidian minimalist design system |
| **Icons** | Bootstrap Icons CDN | Refined hairline vector icons & custom SVG favicon |
| **Data Visualization** | Chart.js CDN | Weekly sales trend & Cash vs GCash revenue doughnut |
| **Currency & Timezone**| Philippine Peso (₱) | Timezone set to `Asia/Manila` |

---

## 🗄️ Database Schema & Architecture

```
                    +------------------------------------+
                    |            app1.users              |
                    | (Shared Auth - Never Alter Table)  |
                    +------------------------------------+
                    | PK idno (VARCHAR)                  |
                    |    username (VARCHAR)              |
                    |    pwd (VARCHAR - bcrypt)          |
                    |    name (VARCHAR)                  |
                    +-----------------+------------------+
                                      | 1
                                      |
                                      | M
+---------------------------+       +------------------------------------+
|   canteen_pos.categories  |       |         canteen_pos.orders         |
+---------------------------+       +------------------------------------+
| PK category_id (SERIAL)   |       | PK order_id (SERIAL)               |
|    category_name (VARCHAR)|       | FK idno -> app1.users(idno)        |
+-------------+-------------+       |    customer_name (VARCHAR)         |
              | 1                   |    order_date (TIMESTAMP)          |
              |                     |    status ('pending','paid','canc')|
              | M                   |    total_amount (NUMERIC 10,2)     |
+-------------+-------------+       +-------+--------------------+-------+
|    canteen_pos.products   |               | 1                  | 1
+---------------------------+               |                    |
| PK product_id (SERIAL)    |               |                    |
| FK category_id            |               | M                  | 1
|    product_name (VARCHAR) |       +-------+-------------+  +---+----------------+
|    price (NUMERIC 10,2)   |<------+ canteen_pos.        |  |  canteen_pos.      |
|    stock_qty (INTEGER)    |       |      order_items    |  |     payments       |
|    is_available (BOOLEAN) |       +---------------------+  +--------------------+
+---------------------------+       | PK order_item_id    |  | PK payment_id      |
                                    | FK order_id         |  | FK order_id (UQ)   |
                                    | FK product_id       |  |    payment_method  |
                                    |    quantity (INT)   |  |    amount_paid     |
                                    |    unit_price       |  |    change_amount   |
                                    |    line_total (GEN) |  |    paid_at         |
                                    +---------------------+  +--------------------+
```

### PostgreSQL Generated Column
`order_items.line_total` is defined as:
```sql
line_total NUMERIC(10, 2) GENERATED ALWAYS AS ((quantity::NUMERIC) * unit_price) STORED
```
The application strictly excludes `line_total` from `$fillable` attributes in Eloquent models, guaranteeing database integrity.

---

## 🚀 Installation & Setup Guide

### 1. Prerequisites
- **PHP 8.2** or higher (with `pdo_pgsql`, `pgsql`, `mbstring`, `openssl`, `bcmath` extensions enabled).
- **Composer** (PHP package manager).
- **PostgreSQL 15+** (tested up to PostgreSQL 18) running on port `5433` (or configured port).

### 2. Clone / Open Repository
```bash
cd "canteen_pos"
```

### 3. Environment Configuration
Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```
Ensure database credentials match your local PostgreSQL server:
```ini
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5433
DB_DATABASE=canteen_pos
DB_USERNAME=postgres
DB_PASSWORD=your_postgres_password

# Keep file-based session and cache (prevents migration tables)
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
APP_TIMEZONE=Asia/Manila
```

### 4. Database Initialization (Standalone SQL)
If setting up a fresh database, execute [`database_schema_and_seed.sql`](database_schema_and_seed.sql) inside PostgreSQL:
```bash
psql -h 127.0.0.1 -p 5433 -U postgres -d canteen_pos -f database_schema_and_seed.sql
```
*(Do NOT run `php artisan migrate`, as the database is pre-structured and avoids polluting the shared PostgreSQL schema).*

### 5. Install Dependencies & Generate App Key
```bash
composer install
php artisan key:generate
```

### 6. Launch Application
You can run the web server using PHP artisan:
```bash
php artisan serve --host=127.0.0.1 --port=8000
```
Or double-click [`start_server.bat`](start_server.bat) on Windows.

Open your browser at: **[http://127.0.0.1:8000](http://127.0.0.1:8000)**

---

## 🔑 Default Cashier Accounts

| Role | Username | Password | ID Number |
| :--- | :--- | :--- | :--- |
| **Lead Cashier** | `admin@school.com` | `password123` | `1001` |
| **Canteen Staff** | `cashier1@school.com` | `password123` | `1002` |

> Passwords use bcrypt hashing via `Hash::make()`, maintaining 100% interoperability with native PHP `password_hash()` across other school project modules.

---

## ⚡ Cashier Keyboard Shortcuts

When operating the Point of Sale terminal (`/pos`):

| Key | Action |
| :--- | :--- |
| <kbd>F2</kbd> | Jump cursor instantly to product search box |
| <kbd>F4</kbd> | Toggle payment method between **Cash** and **GCash** |
| <kbd>Enter</kbd> | Submit & complete checkout (when payment input is focused) |
| <kbd>Esc</kbd> | Close receipt modal or clear active selection |

---

## 📂 Key File Architecture

```
canteen_pos/
├── app/
│   ├── Http/Controllers/
│   │   ├── AuthController.php        # Custom login/register (no Breeze), rate-limited
│   │   ├── DashboardController.php   # Chart.js statistics & low-stock alerts
│   │   ├── OrderController.php       # Transaction history & atomic cancellation/refund
│   │   ├── ProductController.php     # Inventory management & quick restock
│   │   ├── ReceiptController.php     # 80mm printable thermal receipt view
│   │   └── ReportController.php      # Sales reports & CSV spreadsheet export
│   ├── Livewire/
│   │   └── Pos.php                   # Real-time POS component state & validation
│   ├── Models/
│   │   ├── Category.php              # Schema-qualified (canteen_pos.categories)
│   │   ├── Order.php                 # Schema-qualified (canteen_pos.orders)
│   │   ├── OrderItem.php             # Generated line_total protected
│   │   ├── Payment.php               # Schema-qualified (canteen_pos.payments)
│   │   ├── Product.php               # Schema-qualified (canteen_pos.products)
│   │   └── User.php                  # Shared table (app1.users), custom pwd column
│   └── Services/
│       └── CheckoutService.php       # Concurrency locking & atomic DB::transaction
├── database_schema_and_seed.sql      # Standalone PostgreSQL DDL and seed script
├── public/
│   ├── css/style.css                 # Minimalist executive design system
│   └── favicon.svg                   # Vector SVG brand mark
├── resources/views/
│   ├── auth/                         # Clean login & cashier registration
│   ├── layouts/app.blade.php         # Master obsidian header & navigation
│   ├── livewire/pos.blade.php        # POS layout, shortcuts, audio, receipt modal
│   ├── dashboard.blade.php           # KPI metrics, Chart.js trends, top products
│   ├── orders/                       # Audit table, detail modal, void/cancel modal
│   ├── products/                     # Product list, restock modal, add/edit forms
│   └── reports/                      # Revenue breakdown & Excel/CSV export
└── start_server.bat                  # One-click Windows application launcher
```

---

## 🎓 Academic Defense Notes

1. **Why `lockForUpdate()` in `CheckoutService`?**
   In a busy canteen, multiple cashiers may punch an order for the last remaining meal item simultaneously. Standard read-then-write logic causes race conditions resulting in negative stock. `lockForUpdate()` acquires an exclusive row-level lock in PostgreSQL during the transaction, guaranteeing strictly consistent inventory.
2. **Why no Laravel migrations?**
   The project connects to a pre-existing PostgreSQL database with a shared authentication schema (`app1.users`) used by multiple students. Running `artisan migrate` would attempt to create framework tables (`migrations`, `sessions`, `jobs`) in the public schema or alter shared structures. By utilizing schema-qualified models and file-based session/cache storage, the application operates cleanly without leaving unwanted footprints.
3. **How does Order Cancellation work?**
   Cancelling an order runs within an atomic `DB::transaction()` that locks the order and each associated product, increments the inventory counts, restores availability flags if stock was previously zero, marks the order status as `'cancelled'`, and excludes it from all subsequent sales calculations and Chart.js metrics.
