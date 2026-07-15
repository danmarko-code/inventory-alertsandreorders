<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->string('id')->primary(); // Using custom ID strings like 'PC-001'
            $table->string('name');
            $table->string('category');
            $table->integer('currentQty')->default(0);
            $table->integer('minLimit')->default(0);
            $table->integer('maxLimit')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};