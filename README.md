# Alerts & Reorders — Inventory Submodule

A Laravel + MySQL submodule for inventory alert detection and automated purchase order reordering. Part of a larger ERP Inventory system.

## What this submodule does

- Tracks stock levels per product and flags Low Stock / Out of Stock / Overstock automatically
- Lets admins set min/max thresholds per product
- Auto-generates draft purchase orders when a product has "auto-reorder" enabled and hits a shortage
- Manages a full purchase order lifecycle: Draft → Pending → Ordered → Received (or Voided)
- Logs every action (who did what, when) in a persistent Activity Log

## What this submodule does NOT do

- It does not update actual stock quantities when a shipment is received — it only records that a shipment happened (a `stock_movements` entry). Applying that to real inventory counts is handled by a separate **Stock Movements** submodule.
- No real password login. Instead, use a simple "acting as Admin 1/2/3" switcher (no credentials required) for demo/testing purposes.

---

## Requirements

- PHP 8.2+
- Composer
- MySQL 8+
- A local dev environment such as [Laragon](https://laragon.org/) (Windows), Herd (Mac), or XAMPP

---

## Setup instructions

### 1. Clone the repository
```bash
git clone <your-repo-url>
cd inventory-alertsandreorders-main
```

### 2. Install PHP dependencies
```bash
composer install
```

### 3. Create your environment file
```bash
copy .env.example .env
```
*(On Mac/Linux, use `cp .env.example .env` instead)*

### 4. Generate the application key
```bash
php artisan key:generate
```

### 5. Create a MySQL database
Using your database tool (HeidiSQL, phpMyAdmin, or MySQL CLI), create a new empty database — for example:
```sql
CREATE DATABASE inventory_alerts_reorders;
```

### 6. Configure your `.env` database settings
Open `.env` and set:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_alerts_reorders
DB_USERNAME=root
DB_PASSWORD=
```
Use whatever username/password your local MySQL actually requires.

### 7. Run migrations and seed sample data
```bash
php artisan migrate --seed
```
This creates all tables and populates 15 sample inventory items, 3 admin accounts, and starter data.

### 8. Run the initial stock check
Seeding data does not automatically detect alerts — run this once after seeding:
```bash
php artisan stock:check-levels
```

### 9. Start the local server
```bash
php artisan serve
```
Visit **http://127.0.0.1:8000** in your browser.

*(If using Laragon, you can instead just visit your project's `.test` domain, e.g. `http://your-project-name.test`, once Laragon's Apache/Nginx is running.)*

---

## How to use the submodule

### Switching admin accounts
Click the account switcher at the bottom of the sidebar to act as Admin 1, 2, or 3. No password required — this is for demo/testing purposes, tracking who performed which action.

### Managing stock thresholds
On the Inventory table, edit the **Min Limit** and **Max Limit** fields directly per item. Changes save automatically and instantly re-check alert status.

### Turning on auto-reorder
Toggle the **Auto-Reorder** switch on any item's row. When ON, if that item drops into Low Stock or Out of Stock, the system will automatically create a **Draft** purchase order for it — no manual action needed.

### Manually creating a purchase order
Click **Create PO** on any item row, or select multiple items and use **Configure & Run Batch PO** to order several items at once.

### Reviewing the Order Approvals Pipeline
Every purchase order — manual or auto-generated — appears here with a status:

| Status | Meaning | Available action |
|---|---|---|
| Draft | Auto-generated, awaiting admin review | Submit to Pipeline, or Discard |
| Pending | Submitted, awaiting approval | Approve or Void |
| Ordered | Approved, order placed with supplier | Mark as Received |
| Received | Shipment confirmed | *(final state)* |
| Voided | Cancelled | *(final state)* |

**Note:** Discarding an auto-generated Draft also turns OFF auto-reorder for that item, so it won't immediately redraft the same order.

### Refreshing alert detection manually
If you want to re-check stock levels without waiting for the automatic triggers, run:
```bash
php artisan stock:check-levels
```
This runs automatically already when you: mark a PO received, edit a threshold, or toggle auto-reorder — so manual runs are mainly needed right after a fresh `migrate:fresh --seed`.

### Viewing the Activity Log
Every meaningful action (threshold changes, PO approvals, auto-reorder events, alert acknowledgements) is permanently logged and viewable in the Activity Log panel, filterable by admin.

---

## Resetting to a clean state

To wipe all data and start over with fresh sample data:
```bash
php artisan migrate:fresh --seed
php artisan stock:check-levels
```

---

## Project structure quick reference

```
app/Http/Controllers/InventoryAlertController.php   → main submodule logic
app/Http/Controllers/AccountSwitcherController.php  → admin account switching
app/Models/                                          → InventoryItem, ApprovalRequest,
                                                        ApprovalRequestItem, StockAlert,
                                                        StockMovement, ActivityLog, User
app/Services/AutoReorderService.php                  → auto-reorder drafting logic
app/Console/Commands/CheckStockLevels.php            → alert detection command
database/migrations/                                 → all table definitions
database/seeders/                                    → sample data
resources/views/alerts-reorders.blade.php            → main page UI
resources/views/layouts/app.blade.php                → shared sidebar/header layout
routes/web.php                                        → all submodule routes
```
