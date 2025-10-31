<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('searches', function (Blueprint $table) {
            $table->id();
            $table->string('part_number');
            $table->string('part_make')->nullable();
            $table->string('name')->nullable();
            $table->integer('quantity')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->decimal('sell_price', 10, 2)->nullable();
            $table->string('warehouse')->nullable();
            $table->string('supplier')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('searches');
    }
};
