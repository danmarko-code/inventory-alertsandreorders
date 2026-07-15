<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $table = 'inventory_items';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'category',
        'currentQty',
        'minLimit',
        'maxLimit',
        'auto_reorder',
        'reorder_qty',
    ];

    protected $casts = [
        'auto_reorder' => 'boolean',
    ];
}