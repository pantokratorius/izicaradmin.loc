<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('car_brand_id')->nullable();
            $table->unsignedBigInteger('car_model_id')->nullable();
            $table->unsignedBigInteger('car_generation_id')->nullable();
            $table->unsignedBigInteger('car_serie_id')->nullable();
            $table->unsignedBigInteger('car_modification_id')->nullable();
            $table->string('brand_name')->nullable();   // тип транспортного средства
            $table->string('model_name')->nullable();   // тип транспортного средства
            $table->string('generation_name')->nullable();   // тип транспортного средства
            $table->string('serie_name')->nullable();   // тип транспортного средства
            $table->string('modification_name')->nullable();   // тип транспортного средства
            $table->string('vin')->nullable();
            $table->string('vehicle_type')->nullable();   // тип транспортного средства
            $table->string('registration_number')->nullable(); // гос номер
            $table->string('sts')->nullable();
            $table->string('pts')->nullable();
            $table->year('year_of_manufacture')->nullable();
            $table->string('engine_type')->nullable();    // тип двигателя
            $table->string('comment')->nullable();    // тип двигателя
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('cascade'); // связь с клиентом
            $table->timestamps();

             $table->foreign('car_brand_id')->references('id')->on('car_brands')->onDelete('set null');
            $table->foreign('car_model_id')->references('id')->on('car_models')->onDelete('set null');
            $table->foreign('car_generation_id')->references('id')->on('car_generations')->onDelete('set null');
            $table->foreign('car_serie_id')->references('id')->on('car_series')->onDelete('set null');
            $table->foreign('car_modification_id')->references('id')->on('car_modifications')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');

        
    }
};



?>