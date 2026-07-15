<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\ApprovalRequest;
use App\Models\StockAlert;
use App\Models\ActivityLog;
use App\Services\AutoReorderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class InventoryAlertController extends Controller
{
    public function index()
    {
        return view('alerts-reorders');
    }

    public function getData()
    {
        // Auto-reorder runs on every poll: cheap (skips items that already
        // have a Draft/Pending/Ordered PO in flight) and means a draft
        // shows up on the next refresh without needing a scheduled job.
        AutoReorderService::evaluate();

        return response()->json([
            'inventoryItems' => InventoryItem::all(),
            'approvalRequests' => ApprovalRequest::with('requestedBy', 'items.inventoryItem')->orderBy('created_at', 'desc')->get(),
            'stockAlerts' => StockAlert::with('inventoryItem', 'acknowledgedBy')->whereIn('status', ['active', 'acknowledged'])->latest()->get(),
            'activityLogs' => ActivityLog::with('user')->latest()->limit(50)->get(),
        ]);
    }

    public function acknowledgeAlert(StockAlert $alert)
    {
        $alert->update([
            'status' => 'acknowledged',
            'acknowledged_by' => auth()->id(),
            'acknowledged_at' => now(),
        ]);

        ActivityLog::record(
            'alert.acknowledged',
            "Acknowledged the {$alert->severity} {$alert->type} alert for {$alert->inventory_item_id}.",
        );

        return response()->json(['success' => true, 'alert' => $alert]);
    }

    public function resolveAlert(StockAlert $alert)
    {
        $alert->update(['status' => 'resolved', 'resolved_at' => now()]);

        ActivityLog::record(
            'alert.resolved',
            "Manually resolved the {$alert->severity} {$alert->type} alert for {$alert->inventory_item_id}.",
        );

        return response()->json(['success' => true, 'alert' => $alert]);
    }

    public function updateLimits(Request $request, $id)
    {
        $request->validate([
            'target' => 'required|in:min,max',
            'value' => 'required|integer|min:0'
        ]);

        $item = InventoryItem::findOrFail($id);
        $oldValue = $request->target === 'min' ? $item->minLimit : $item->maxLimit;

        if ($request->target === 'min') {
            $item->minLimit = $request->value;
        } else {
            $item->maxLimit = $request->value;
        }

        $item->save();

        ActivityLog::record(
            'threshold.updated',
            "Changed {$request->target} limit for {$item->name} ({$item->id}) from {$oldValue} to {$request->value}.",
        );

        // FIX: changing a threshold can make an existing alert stale in
        // either direction — raising min could newly flag a previously-fine
        // item, lowering it could resolve one that no longer qualifies.
        Artisan::call('stock:check-levels');

        return response()->json(['success' => true, 'item' => $item->fresh()]);
    }

    public function toggleAutoReorder(Request $request, $id)
    {
        $request->validate(['enabled' => 'required|boolean']);

        $item = InventoryItem::findOrFail($id);
        $item->auto_reorder = $request->boolean('enabled');
        $item->save();

        ActivityLog::record(
            'auto_reorder.toggled',
            "Turned auto-reorder " . ($item->auto_reorder ? 'ON' : 'OFF') . " for {$item->name} ({$item->id}).",
        );

        // FIX: evaluating auto-reorder against whatever alerts already
        // happen to exist is unreliable — an alert can be stale (e.g. the
        // item was already restocked but nothing re-checked it yet). Run
        // the real detection command, which resolves alerts that no longer
        // apply, refreshes/creates ones that still do, AND evaluates
        // auto-reorder against that accurate picture — it does all three
        // in one pass now (see CheckStockLevels).
        $draftsBefore = ApprovalRequest::where('source', 'auto')->count();
        Artisan::call('stock:check-levels');
        $draftsAfter = ApprovalRequest::where('source', 'auto')->count();
        $created = $draftsAfter - $draftsBefore;

        return response()->json([
            'success' => true,
            'item' => $item,
            'draftsCreated' => $created,
        ]);
    }

    public function submitDraft($id)
    {
        $pipeline = ApprovalRequest::findOrFail($id);

        if ($pipeline->status !== 'Draft') {
            return response()->json(['success' => false, 'message' => 'Only a Draft order can be submitted to the pipeline.'], 400);
        }

        $pipeline->status = 'Pending';
        $pipeline->save();

        ActivityLog::record(
            'po.draft_submitted',
            "Reviewed and submitted auto-generated draft PO #{$pipeline->reqId} into the approval pipeline.",
        );

        return response()->json(['success' => true, 'request' => $pipeline]);
    }

    public function discardDraft($id)
    {
        $pipeline = ApprovalRequest::findOrFail($id);

        if ($pipeline->status !== 'Draft') {
            return response()->json(['success' => false, 'message' => 'Only a Draft order can be discarded.'], 400);
        }

        $pipeline->status = 'Voided';
        $pipeline->save();

        ActivityLog::record(
            'po.draft_discarded',
            "Discarded auto-generated draft PO #{$pipeline->reqId}.",
            'error'
        );

        // FIX: without this, the item's auto-reorder stays ON, so the very
        // next stock:check-levels run just drafts another identical PO for
        // the same alert — discarding never actually stuck. Declining an
        // auto-draft is treated as "stop auto-ordering this item," not just
        // "delete this one attempt."
        $autoReorderTurnedOff = false;
        if ($pipeline->source === 'auto') {
            $itemIds = $pipeline->items->pluck('inventory_item_id')->unique();
            foreach ($itemIds as $itemId) {
                $item = InventoryItem::find($itemId);
                if ($item && $item->auto_reorder) {
                    $item->auto_reorder = false;
                    $item->save();
                    $autoReorderTurnedOff = true;

                    ActivityLog::record(
                        'auto_reorder.toggled',
                        "Turned auto-reorder OFF for {$item->name} ({$item->id}) — its auto-generated draft PO #{$pipeline->reqId} was discarded.",
                    );
                }
            }
        }

        return response()->json([
            'success' => true,
            'request' => $pipeline,
            'autoReorderTurnedOff' => $autoReorderTurnedOff,
        ]);
    }

    /**
     * DEMO/TEST HELPER: forces one item's quantity into a Low Stock or
     * Out of Stock situation, then runs the exact same `stock:check-levels`
     * command your real stock-monitoring process runs — so the resulting
     * StockAlert has an authentic type/severity, not a guessed one — and
     * finally runs AutoReorderService::evaluate(), the same call getData()
     * makes on every poll. Lets you watch a draft PO appear end-to-end
     * without waiting on real stock movement.
     */
    public function submitPO(Request $request)
    {
        $request->validate([
            'details' => 'required|string',
            'supplier' => 'required|string',
            'warehouse' => 'required|string',
            'itemsArray' => 'required|array',
        ]);

        // SECURITY FIX: 'requester' used to be trusted straight from the
        // request body — anyone could submit a PO claiming to be any name.
        // Now it's derived server-side from whoever is the current acting
        // admin (auth()->user(), set by the ResolveActingAdmin middleware).
        $actingAdmin = auth()->user();

        $newRequest = ApprovalRequest::create([
            'timestamp' => now()->format('Y-m-d H:i'),
            'requester' => $actingAdmin->name ?? 'Unknown',
            'requested_by' => $actingAdmin->id ?? null,
            'details' => $request->details,
            'supplier' => $request->supplier,
            'warehouse' => $request->warehouse,
            'status' => 'Pending',
            'itemsArray' => $request->itemsArray, // kept during transition, see model note
        ]);

        // NEW: also write proper relational rows. Once you've confirmed the
        // whole app reads from ->items instead of ->itemsArray, this can be
        // the *only* write, and itemsArray/the JSON column can be dropped.
        foreach ($request->itemsArray as $item) {
            if (!isset($item['id'], $item['qty'])) continue;
            $newRequest->items()->create([
                'inventory_item_id' => $item['id'],
                'qty' => $item['qty'],
            ]);
        }

        ActivityLog::record(
            'po.submitted',
            "Submitted a new purchase order #{$newRequest->reqId} — {$request->details}.",
        );

        return response()->json(['success' => true, 'request' => $newRequest->load('items.inventoryItem')]);
    }

    public function processPipeline(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Approved,Voided'
        ]);

        $pipeline = ApprovalRequest::findOrFail($id);

        if ($pipeline->status !== 'Pending') {
            return response()->json(['success' => false, 'message' => 'Order is already processed.'], 400);
        }

        if ($request->status === 'Voided') {
            $pipeline->status = 'Voided';
            $pipeline->save();

            ActivityLog::record(
                'po.voided',
                "Voided purchase order #{$pipeline->reqId}.",
                'error'
            );

            return response()->json(['success' => true, 'action' => 'Voided']);
        }

        // FIX: Approving a PO means the order has been placed with the
        // supplier — it does NOT mean the stock has arrived yet. Stock
        // quantities are untouched here. They only change when someone
        // later confirms the shipment via markReceived() below. This
        // keeps "ordering" and "receiving" as two separate real-world
        // events, matching how Stock Transactions should actually work.
        $pipeline->status = 'Ordered';
        $pipeline->save();

        ActivityLog::record(
            'po.approved',
            "Approved purchase order #{$pipeline->reqId} — order placed with {$pipeline->supplier}. Awaiting delivery.",
            'success'
        );

        return response()->json(['success' => true, 'action' => 'Ordered']);
    }

    public function markReceived(Request $request, $id)
    {
        $pipeline = ApprovalRequest::findOrFail($id);

        if ($pipeline->status !== 'Ordered') {
            return response()->json(['success' => false, 'message' => 'Only an Ordered request can be marked as Received.'], 400);
        }

        // This is now the ONLY place that actually changes stock quantities
        // for a purchase order — the real-world moment the shipment arrives.
        $orderItems = $pipeline->items->count()
            ? $pipeline->items->map(fn ($i) => ['id' => $i->inventory_item_id, 'qty' => $i->qty])->toArray()
            : $pipeline->itemsArray;

        foreach ($orderItems as $orderItem) {
            $invItem = InventoryItem::find($orderItem['id']);
            if ($invItem && ($invItem->currentQty > $invItem->maxLimit)) {
                return response()->json([
                    'success' => false,
                    'message' => "Item {$invItem->id} is already over stock limits."
                ], 422);
            }
        }

        foreach ($orderItems as $orderItem) {
            $invItem = InventoryItem::find($orderItem['id']);
            if ($invItem) {
                $invItem->currentQty += $orderItem['qty'];
                $invItem->save();
            }
        }

        $pipeline->status = 'Received';
        $pipeline->save();

        // FIX: receiving stock used to leave the associated stock_alerts row
        // untouched — it stayed "active" with the old out_of_stock/low_stock
        // type forever, since nothing re-checked it. That meant auto-reorder
        // kept treating the item as still short even after real stock was
        // updated above, and could draft ANOTHER unnecessary PO once this
        // one left "in flight" status. Re-running detection here resolves
        // any alert that's no longer accurate now that currentQty changed.
        Artisan::call('stock:check-levels');

        ActivityLog::record(
            'po.received',
            "Marked purchase order #{$pipeline->reqId} as Received — stock levels updated.",
            'success'
        );

        return response()->json(['success' => true, 'action' => 'Received']);
    }
}
