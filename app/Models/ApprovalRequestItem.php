<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalRequestItem extends Model
{
    protected $fillable = ['approval_request_id', 'inventory_item_id', 'qty'];

    public function approvalRequest()
    {
        return $this->belongsTo(ApprovalRequest::class, 'approval_request_id', 'reqId');
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id', 'id');
    }
}
