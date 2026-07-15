<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Per-item on/off switch, plus an optional fixed reorder quantity.
        // If reorder_qty is left null, the service falls back to
        // (maxLimit - currentQty) at the moment the draft is created.
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->boolean('auto_reorder')->default(false)->after('maxLimit');
            $table->unsignedInteger('reorder_qty')->nullable()->after('auto_reorder');
        });

        // Distinguish system-generated drafts from manually created orders,
        // and keep a traceable link back to the alert that caused it.
        Schema::table('approval_requests', function (Blueprint $table) {
            $table->string('source')->default('manual')->after('status'); // manual | auto
            $table->foreignId('triggered_by_alert_id')
                ->nullable()
                ->after('source')
                ->constrained('stock_alerts')
                ->nullOnDelete();
        });

        // NOTE ON STATUS VALUES: `approval_requests.status` is a plain string
        // column (no DB-level enum), so no migration is needed to support
        // the new "Draft" status used by auto-reorder — it's just a new
        // string value alongside the existing Pending/Ordered/Received/Voided.
    }

    public function down(): void
    {
        Schema::table('approval_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('triggered_by_alert_id');
            $table->dropColumn('source');
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn(['auto_reorder', 'reorder_qty']);
        });
    }
};
