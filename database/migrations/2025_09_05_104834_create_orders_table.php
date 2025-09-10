<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_number')->unique();        // Номер заказа
            // $table->decimal('amount', 10, 2)->nullable();               // Сумма заказа
            // $table->dateTime('created_at');                 // Дата создания
            $table->unsignedBigInteger('vehicle_id')->nullable(); // Автомобиль на который был заказ
            $table->unsignedBigInteger('client_id')->nullable();  // Если заказ к клиенту напрямую
            $table->unsignedBigInteger('manager_id')->nullable(); // Ответственный менеджер
            $table->tinyInteger('status')->default(1);
            $table->integer('mileage')->nullable();             // Пробег
            $table->decimal('margin', 5, 2)->default(null)->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('vehicle_id')
                  ->references('id')
                  ->on('vehicles')
                  ->onDelete('cascade');

            $table->foreign('client_id')
                  ->references('id')
                  ->on('clients')
                  ->onDelete('cascade');

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
