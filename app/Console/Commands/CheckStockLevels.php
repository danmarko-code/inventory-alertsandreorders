<?php

namespace App\Console\Commands;

use App\Models\InventoryItem;
use App\Models\StockAlert;
use App\Services\AutoReorderService;
use Illuminate\Console\Command;

class CheckStockLevels extends Command
{
    protected $signature = 'stock:check-levels';
    protected $description = 'Compare inventory_items current quantities against their own min/max limits, generate/resolve alerts, and auto-create draft POs for items with auto-reorder enabled';

    public function handle(): int
    {
        $created = 0;
        $resolved = 0;

        InventoryItem::all()->each(function (InventoryItem $item) use (&$created, &$resolved) {
            $qty = $item->currentQty;

            $existingActive = StockAlert::where('inventory_item_id', $item->id)
                ->where('status', 'active')
                ->first();

            if ($qty <= 0) {
                $this->upsertAlert($item, 'out_of_stock', 'critical', $qty, $item->minLimit, $existingActive, $created);
            } elseif ($qty < $item->minLimit) {
                $ratio = $qty / max(1, $item->minLimit);
                $severity = $ratio < 0.5 ? 'high' : 'medium';
                $this->upsertAlert($item, 'low_stock', $severity, $qty, $item->minLimit, $existingActive, $created);
            } elseif ($qty > $item->maxLimit) {
                $this->upsertAlert($item, 'overstock', 'medium', $qty, $item->maxLimit, $existingActive, $created);
            } elseif ($existingActive) {
                // Back within range — auto-resolve
                $existingActive->update(['status' => 'resolved', 'resolved_at' => now()]);
                $resolved++;
            }
        });

        // FIX: this used to only detect/store alerts and stop there — the
        // actual auto-reorder drafting logic (AutoReorderService) was never
        // called from here, only from the separate "simulate" debug widget.
        // This is now the real automated path: run this command (manually,
        // or on a schedule/cron) and it both detects alerts AND drafts POs
        // for any item that has auto-reorder enabled.
        $draftsCreated = AutoReorderService::evaluate();

        $this->info("Checked inventory items. Alerts created/updated: {$created}. Auto-resolved: {$resolved}. Auto-reorder drafts created: " . count($draftsCreated) . ".");
        return self::SUCCESS;
    }

    private function upsertAlert(InventoryItem $item, string $type, string $severity, int $qty, int $thresholdQty, ?StockAlert $existing, int &$created): void
    {
        if ($existing && $existing->type === $type) {
            // Same situation still ongoing — just refresh the numbers, no duplicate alert
            $existing->update(['current_qty' => $qty, 'severity' => $severity]);
            return;
        }

        if ($existing) {
            // Situation changed type (e.g. low_stock -> out_of_stock) — close old, open new
            $existing->update(['status' => 'resolved', 'resolved_at' => now()]);
        }

        StockAlert::create([
            'inventory_item_id' => $item->id,
            'type' => $type,
            'severity' => $severity,
            'current_qty' => $qty,
            'threshold_qty' => $thresholdQty,
            'status' => 'active',
        ]);

        $created++;
    }
}
