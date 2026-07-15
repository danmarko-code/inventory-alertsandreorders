<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalRequest extends Model
{
    protected $table = 'approval_requests';
    protected $primaryKey = 'reqId';

    protected $fillable = [
        'timestamp',
        'requester',       // kept temporarily for backwards compatibility, see note below
        'requested_by',
        'details',
        'supplier',
        'warehouse',
        'status',
        'itemsArray',
        'source',                 // 'manual' | 'auto'
        'triggered_by_alert_id',
    ];

    protected $casts = [
        'itemsArray' => 'array',
    ];

    // The real source of truth for "who submitted this" going forward.
    // Never trust the old 'requester' text field for anything security-relevant.
    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    // Traceability back to the alert that caused an auto-generated draft.
    public function triggeredByAlert()
    {
        return $this->belongsTo(StockAlert::class, 'triggered_by_alert_id');
    }

    // NEW: proper relational items, replacing the old itemsArray JSON blob.
    // itemsArray is kept on the table temporarily for backwards compatibility
    // during the transition — see MigrateItemsArrayToRelationalTable command.
    public function items()
    {
        return $this->hasMany(ApprovalRequestItem::class, 'approval_request_id', 'reqId');
    }
}
