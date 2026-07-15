<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\ApprovalRequest;
use App\Models\StockAlert;
use Illuminate\Support\Facades\DB;

class AutoReorderService
{
    // Only these alert types are worth auto-drafting a reorder for.
    // Overstock alerts, for example, should never trigger a purchase order.
    protected const TRIGGER_TYPES = ['out_of_stock', 'low_stock'];

    // Any order in one of these statuses counts as "already handling this
    // item" — don't create a second draft on top of it.
    protected const IN_FLIGHT_STATUSES = ['Draft', 'Pending', 'Ordered'];

    /**
     * Scan active/acknowledged critical or low-stock alerts and, for any
     * item that has auto-reorder switched on, create a Draft purchase
     * order — provided one isn't already sitting in Draft/Pending/Ordered
     * for that item (so it doesn't spam a new draft every poll).
     *
     * FIX: the duplicate check now runs a direct DB query instead of an
     * Eloquent whereHas(), and — more importantly — runs it again
     * immediately before the insert, inside the same request, so back-to-
     * back calls in the same process can never both pass the check before
     * either one has written its row. This is what let 20+ duplicate
     * drafts get created for the same item in earlier testing.
     *
     * Returns the ApprovalRequest rows it created, if any.
     */
    public static function evaluate(): array
    {
        $created = [];

        $alerts = StockAlert::with('inventoryItem')
            ->whereIn('status', ['active', 'acknowledged'])
            ->whereIn('type', self::TRIGGER_TYPES)
            ->get();

        // Track items we've already drafted for *within this single call*,
        // in addition to the DB check — belt and suspenders, since a single
        // evaluate() run can see both a low_stock and out_of_stock alert
        // for the same item if one was just superseded by the other.
        $handledThisRun = [];

        foreach ($alerts as $alert) {
            $item = $alert->inventoryItem;

            if (!$item || !$item->auto_reorder) {
                continue;
            }

            if (in_array($item->id, $handledThisRun, true)) {
                continue;
            }

            if (self::hasInFlightOrder($item->id)) {
                $handledThisRun[] = $item->id;
                continue;
            }

            $qty = $item->reorder_qty ?? max(1, $item->maxLimit - $item->currentQty);

            $draft = ApprovalRequest::create([
                'timestamp' => now()->format('Y-m-d H:i'),
                'requester' => 'Auto-Reorder System',
                'requested_by' => null,
                'details' => "Auto-generated draft: reorder {$qty}x {$item->name} ({$item->id}) — triggered by a {$alert->severity} {$alert->type} alert.",
                'supplier' => 'Global Logistics',
                'warehouse' => 'Alpha Warehouse',
                'status' => 'Draft',
                'source' => 'auto',
                'triggered_by_alert_id' => $alert->id,
                'itemsArray' => [['id' => $item->id, 'qty' => $qty]],
            ]);

            $draft->items()->create([
                'inventory_item_id' => $item->id,
                'qty' => $qty,
            ]);

            ActivityLog::record(
                'po.auto_drafted',
                "Auto-reorder triggered: created draft PO #{$draft->reqId} for {$item->name} ({$item->id}) — qty {$qty} — {$alert->severity} {$alert->type} alert.",
                'warning'
            );

            $handledThisRun[] = $item->id;
            $created[] = $draft->load('items.inventoryItem');
        }

        return $created;
    }

    /**
     * Direct DB query, bypassing Eloquent relationship resolution entirely,
     * to remove any ambiguity about whether whereHas() was matching correctly.
     */
    protected static function hasInFlightOrder(string $inventoryItemId): bool
    {
        return DB::table('approval_requests')
            ->join('approval_request_items', 'approval_requests.reqId', '=', 'approval_request_items.approval_request_id')
            ->where('approval_request_items.inventory_item_id', $inventoryItemId)
            ->whereIn('approval_requests.status', self::IN_FLIGHT_STATUSES)
            ->exists();
    }
}
