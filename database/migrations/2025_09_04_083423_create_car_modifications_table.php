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
        Schema::create('car_modifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_model_id')->constrained('car_models')->onDelete('cascade');
            $table->foreignId('car_serie_id')->constrained('car_series')->onDelete('cascade');
            $table->string('name');
            $table->integer('start_production_year')->nullable();
            $table->integer('end_production_year')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_modifications');
    }
};
