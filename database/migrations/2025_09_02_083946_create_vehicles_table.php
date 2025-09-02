<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vin')->unique();
            $table->string('vehicle_type')->nullable();   // тип транспортного средства
            $table->string('brand')->nullable();          // бренд
            $table->string('model')->nullable();          // модель
            $table->string('generation')->nullable(); // поколение
            $table->string('body')->nullable();       // кузов
            $table->string('modification')->nullable(); // модификация
            $table->string('registration_number')->nullable(); // гос номер
            $table->string('sts')->nullable();
            $table->string('pts')->nullable();
            $table->year('year_of_manufacture')->nullable();
            $table->string('engine_type')->nullable();    // тип двигателя
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('cascade'); // связь с клиентом
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};



?>