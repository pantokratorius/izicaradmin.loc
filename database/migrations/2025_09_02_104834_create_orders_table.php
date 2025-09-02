<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();        // Номер заказа
            $table->decimal('amount', 10, 2);               // Сумма заказа
            // $table->dateTime('created_at');                 // Дата создания
            $table->unsignedBigInteger('vehicle_id')->nullable(); // Автомобиль на который был заказ
            $table->unsignedBigInteger('client_id')->nullable();  // Если заказ к клиенту напрямую
            $table->unsignedBigInteger('manager_id')->nullable(); // Ответственный менеджер
            $table->integer('mileage')->nullable();             // Пробег
            $table->timestamps();

            // Foreign keys
            $table->foreign('vehicle_id')
                  ->references('id')
                  ->on('vehicles')
                  ->onDelete('set null');

            $table->foreign('client_id')
                  ->references('id')
                  ->on('clients')
                  ->onDelete('set null');

            $table->foreign('manager_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
