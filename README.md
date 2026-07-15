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
cp .env.example .env
```

### 4. Generate the application key
```bash
php artisan key:generate
```

### 5. Create a MySQL database
```sql
CREATE DATABASE inventory_alerts_reorders;
```

### 6. Configure your `.env` database settings
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_alerts_reorders
DB_USERNAME=root
DB_PASSWORD=
```

### 7. Run migrations and seed sample data
```bash
php artisan migrate --seed
```

### 8. Run the initial stock check
```bash
php artisan stock:check-levels
```

### 9. Start the local server
```bash
php artisan serve
```
Visit **http://127.0.0.1:8000**, or your Laragon `.test` domain.

---

## How to use the submodule

### Switching admin accounts
Click the account switcher at the bottom of the sidebar to act as Admin 1, 2, or 3. No password required.

### Managing stock thresholds
Edit **Min Limit** / **Max Limit** directly on any item row. Saves automatically and re-checks alert status.

### Turning on auto-reorder
Toggle the **Auto-Reorder** switch on any item. When ON, a shortage automatically creates a **Draft** purchase order.

### Manually creating a purchase order
Use **Create PO** per item, or select multiple and use **Configure & Run Batch PO**.

### Order Approvals Pipeline statuses

| Status | Meaning | Available action |
|---|---|---|
| Draft | Auto-generated, awaiting review | Submit to Pipeline, or Discard |
| Pending | Submitted, awaiting approval | Approve or Void |
| Ordered | Approved, placed with supplier | Mark as Received |
| Received | Shipment confirmed | *(final state)* |
| Voided | Cancelled | *(final state)* |

**Note:** Discarding an auto-generated Draft also turns OFF auto-reorder for that item.

### Manually refreshing alert detection
```bash
php artisan stock:check-levels
```
Runs automatically already on: marking a PO received, editing a threshold, or toggling auto-reorder.

### Activity Log
Every action is permanently logged and filterable by admin.

---

## Resetting to a clean state
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

---

## Database schema

7 tables specific to this submodule (plus Laravel's default framework tables: `cache`, `jobs`, `failed_jobs`, `password_reset_tokens`, `sessions` — not part of the ERD, included automatically).

### `users`
| Column | Type | Notes |
|---|---|---|
| id | bigint, PK | |
| name | string | e.g. "Admin 1" |
| email | string | |
| role | string | "admin" |
| avatar_color | string | hex color for the UI avatar chip |

### `inventory_items`
| Column | Type | Notes |
|---|---|---|
| id | string, PK | e.g. "PC-001" (not auto-incrementing) |
| name | string | |
| category | string | |
| currentQty | int | |
| minLimit | int | |
| maxLimit | int | |
| auto_reorder | boolean | if true, shortages auto-draft a PO |
| reorder_qty | int, nullable | qty to order when auto-reorder fires |

### `stock_alerts`
| Column | Type | Notes |
|---|---|---|
| id | bigint, PK | |
| inventory_item_id | string, FK → inventory_items | |
| type | string | out_of_stock / low_stock / overstock |
| severity | string | critical / high / medium |
| current_qty, threshold_qty | int | snapshot at time of detection |
| status | string | active / acknowledged / resolved |
| acknowledged_by | bigint, FK → users, nullable | |

### `approval_requests`
| Column | Type | Notes |
|---|---|---|
| reqId | bigint, PK | (custom primary key name) |
| requester | string | display name, derived server-side |
| requested_by | bigint, FK → users, nullable | |
| status | string | Draft / Pending / Ordered / Received / Voided |
| source | string | "manual" or "auto" |
| triggered_by_alert_id | bigint, FK → stock_alerts, nullable | set when auto-generated |
| supplier, warehouse | string | |
| itemsArray | json | legacy, kept for backwards compatibility only |

### `approval_request_items`
| Column | Type | Notes |
|---|---|---|
| id | bigint, PK | |
| approval_request_id | bigint, FK → approval_requests.reqId | |
| inventory_item_id | string, FK → inventory_items | |
| qty | int | |

### `stock_movements`
| Column | Type | Notes |
|---|---|---|
| id | bigint, PK | |
| inventory_item_id | string, FK → inventory_items | |
| type | string | e.g. "receipt" |
| qty | int | |
| source_type | string | e.g. "purchase_order" |
| source_id | bigint, nullable | e.g. the approval_requests.reqId |
| created_by | bigint, FK → users, nullable | |

### `activity_logs`
| Column | Type | Notes |
|---|---|---|
| id | bigint, PK | |
| user_id | bigint, FK → users, nullable | null = system-generated entry |
| action | string | e.g. "po.approved" |
| type | string | info / success / error |
| description | string | human-readable summary |

### Relationships summary
- One **user** can make many approval requests, acknowledge many alerts, record many stock movements, and perform many activity log entries
- One **inventory item** can have many stock alerts, appear in many approval request line items, and have many stock movements
- One **approval request** has many line items (`approval_request_items`)
- One **stock alert** can trigger one approval request (when auto-reorder creates a draft from it)

---


Everything below was added/changed on top of the original submodule, in the order it happened.

### 1. Admin identity & session-based account switching
- Added `role`, `avatar_color` to `users`
- Added `AdminUsersSeeder` (Admin 1/2/3, no real passwords used — session-based only)
- Added `ResolveActingAdmin` middleware — resolves the current "acting admin" from session, no login form
- Added `AccountSwitcherController` — `POST /account/switch`
- Added the account switcher dropdown to the sidebar in `layouts/app.blade.php`

### 2. Purchase order data integrity
- **Problem:** `requester` was a free-text field trusted straight from the browser — spoofable by anyone
- **Fix:** `requester` is now derived server-side from the acting admin; added `requested_by` foreign key to `users`
- **Problem:** order line items were stored as a raw JSON blob (`itemsArray`) with no relational integrity
- **Fix:** added `approval_request_items` table + `ApprovalRequestItem` model; `submitPO()` now writes to both (transition period), `processPipeline()` prefers the relational rows

### 3. Real, persisted stock alerts
- **Problem:** low/out-of-stock/overstock status was calculated live in the browser every page load — nothing was ever stored
- **Fix:** added `stock_alerts` table + `StockAlert` model, and the `stock:check-levels` Artisan command, which detects shortages/overstock and auto-resolves alerts once stock recovers

### 4. Separated "ordering" from "receiving"
- **Problem:** approving a PO instantly added quantity to stock — as if approval and physical delivery were the same moment
- **Fix:** approving now sets status to `Ordered` only (no stock change). Added a separate `markReceived()` action/button — this was the only place stock changed, until change #7 below

### 5. Persistent Activity Log
- **Problem:** the Activity Log panel only lived in browser memory (`addTransactionLog()`), resetting on every page refresh
- **Fix:** added `activity_logs` table + `ActivityLog` model with a `record()` helper. Every mutating action (thresholds, PO approvals, receiving, alert acknowledgement) now logs persistently and is rendered from real server data

### 6. Automated replenishment (auto-reorder)
- Added `auto_reorder`, `reorder_qty` columns to `inventory_items`
- Added `AutoReorderService` — scans active low/out-of-stock alerts and auto-drafts a PO for any item with auto-reorder enabled, skipping items that already have an order "in flight" (Draft/Pending/Ordered) to avoid duplicates
- Wired into `stock:check-levels`, so the same command that detects alerts also drafts reorders in one pass
- Added per-item toggle switch, plus `submitDraft()` / `discardDraft()` actions on the pipeline board

### 7. Bug fixes — duplicate drafts & stale alerts
- **Problem:** duplicate check in `AutoReorderService` occasionally allowed multiple drafts for the same item
- **Fix:** rewrote the "already in flight" check to use a direct DB query instead of an Eloquent relationship lookup, plus an in-memory guard within a single run. Added `reorders:cleanup-duplicates` one-time cleanup command
- **Problem:** alerts went stale — e.g. after receiving stock, the alert stayed "active" forever since nothing re-checked it, causing auto-reorder to keep firing on already-resolved shortages
- **Fix:** `markReceived()`, `toggleAutoReorder()`, and `updateLimits()` all now re-run `stock:check-levels` after they change anything that could affect alert accuracy

### 8. Removed the "Simulate Stock Event" debug widget
- Fully removed the floating widget UI, its JS functions, its route, and its controller method (`simulateStockEvent`) — this was a testing/demo tool, not part of the submodule's real functionality

### 9. Scope separation — stock quantity ownership
- **Decision:** this submodule should only detect shortages and manage the ordering workflow — not own actual stock quantity changes, since a separate Stock Movements submodule will own that
- **Fix:** added `stock_movements` table + `StockMovement` model. `markReceived()` no longer touches `inventory_items.currentQty` at all — it now records a `stock_movements` row (a handoff record) and leaves applying it to real stock as the Stock Movements submodule's responsibility
- Updated the "Shipment Received" popup message to accurately reflect this (previously incorrectly claimed "stock levels have been updated")

### 10. Discard-draft UX fix
- **Problem:** discarding an auto-generated draft PO didn't turn off auto-reorder for that item, so the very next check would just draft an identical PO again
- **Fix:** `discardDraft()` now automatically disables `auto_reorder` on the affected item when an auto-generated draft is declined, logs it, and shows a clear confirmation popup

### Known limitations (not yet addressed)
- No real password-based login (intentional, by design choice)
- No pagination on any table (fine at current data scale)
- `itemsArray` JSON column on `approval_requests` is still present for backwards compatibility alongside the relational `approval_request_items` table
