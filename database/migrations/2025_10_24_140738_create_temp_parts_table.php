<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('temo_parts', function (Blueprint $table) {
            $table->id();

            // Common fields for both stocks and order_items
            $table->string('brand')->nullable();
            $table->string('article')->nullable();
            $table->string('name')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->integer('quantity')->nullable();

            // Source info (optional)
            $table->unsignedBigInteger('user_id')->nullable(); // who added it
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('temo_parts');
    }
};
