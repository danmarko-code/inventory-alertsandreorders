<?php

namespace App\Console\Commands;

use App\Models\ApprovalRequest;
use Illuminate\Console\Command;

class CleanupDuplicateAutoDrafts extends Command
{
    protected $signature = 'reorders:cleanup-duplicates';
    protected $description = 'One-time cleanup: remove duplicate auto-generated Draft purchase orders, keeping only the oldest one per item';

    public function handle(): int
    {
        $drafts = ApprovalRequest::with('items')
            ->where('status', 'Draft')
            ->where('source', 'auto')
            ->orderBy('reqId') // oldest first
            ->get();

        $seenItems = [];
        $deleted = 0;

        foreach ($drafts as $draft) {
            $itemIds = $draft->items->pluck('inventory_item_id')->all();

            // Auto-drafts are always single-item, but this handles it safely either way.
            $isDuplicate = false;
            foreach ($itemIds as $itemId) {
                if (in_array($itemId, $seenItems, true)) {
                    $isDuplicate = true;
                } else {
                    $seenItems[] = $itemId;
                }
            }

            if ($isDuplicate) {
                $draft->items()->delete();
                $draft->delete();
                $deleted++;
            }
        }

        $this->info("Removed {$deleted} duplicate auto-generated draft(s). Kept the oldest draft per item.");
        return self::SUCCESS;
    }
}
