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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            $table->string('first_name')->nullable();   // имя
            $table->string('last_name')->nullable();    // фамилия
            $table->string('middle_name')->nullable(); // отчество
            $table->string('phone')->unique()->nullable(); // телефон
            $table->string('email')->unique()->nullable(); // email
            $table->string('segment')->nullable(); // сегмент
            $table->decimal('discount', 5, 2)->default(0)->nullable(); // скидка (например, 10.50%)
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
