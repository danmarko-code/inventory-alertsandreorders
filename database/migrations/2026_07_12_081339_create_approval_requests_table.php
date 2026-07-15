<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id('reqId');
            $table->string('timestamp');
            $table->string('requester');
            $table->text('details');
            $table->string('supplier');
            $table->string('warehouse');
            $table->string('status')->default('Pending');
            $table->json('itemsArray'); // Store selected item IDs and quantities
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_requests');
    }
};