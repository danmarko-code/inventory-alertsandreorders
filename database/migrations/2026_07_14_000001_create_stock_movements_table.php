<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('inventory_item_id');
            $table->foreign('inventory_item_id')->references('id')->on('inventory_items')->cascadeOnDelete();
            $table->string('type'); // e.g. 'receipt' — kept as a plain string so
                                     // the Stock Movements submodule can define
                                     // its own full set of types later without
                                     // this table needing another migration.
            $table->integer('qty');
            $table->string('source_type'); // e.g. 'purchase_order'
            $table->unsignedBigInteger('source_id')->nullable(); // e.g. the approval_requests.reqId
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            // NOTE: no "applied" boolean here on purpose. Whether/when this
            // movement gets reflected in inventory_items.currentQty is the
            // Stock Movements submodule's decision, not ours — Alerts &
            // Reorders only records that a receipt event happened.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
