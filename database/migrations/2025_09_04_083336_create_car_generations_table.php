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
        Schema::create('car_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_model_id')->constrained('car_models')->onDelete('cascade');
            $table->string('name');
            $table->string('year_begin')->nullable();
            $table->string('year_end')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_generations');
    }
};
