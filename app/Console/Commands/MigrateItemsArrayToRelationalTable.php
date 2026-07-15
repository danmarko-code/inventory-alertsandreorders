<?php

namespace App\Console\Commands;

use App\Models\ApprovalRequest;
use App\Models\ApprovalRequestItem;
use Illuminate\Console\Command;

class MigrateItemsArrayToRelationalTable extends Command
{
    protected $signature = 'approvals:migrate-items-array';
    protected $description = 'One-time backfill: copy ApprovalRequest.itemsArray JSON into the approval_request_items table';

    public function handle(): int
    {
        $count = 0;

        ApprovalRequest::whereNotNull('itemsArray')->each(function (ApprovalRequest $request) use (&$count) {
            foreach ($request->itemsArray as $item) {
                if (!isset($item['id'], $item['qty'])) continue;

                ApprovalRequestItem::firstOrCreate([
                    'approval_request_id' => $request->reqId,
                    'inventory_item_id' => $item['id'],
                ], [
                    'qty' => $item['qty'],
                ]);
                $count++;
            }
        });

        $this->info("Migrated {$count} item rows into approval_request_items.");
        return self::SUCCESS;
    }
}
