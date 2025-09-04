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
        Schema::create('car_series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_model_id')->constrained('car_models')->onDelete('cascade');
            $table->foreignId('car_generation_id')->nullable()->constrained('car_generations')->onDelete('set null');
            $table->string('name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_series');
    }
};
