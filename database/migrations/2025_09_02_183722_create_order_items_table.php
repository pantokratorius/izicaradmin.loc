<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id'); // связь с заказом
            $table->string('part_name');            // Наименование запчасти
            $table->decimal('sale_price', 10, 2)->nullable();   // Цена продажи
            $table->decimal('purchase_price', 10, 2)->nullable(); // Цена закупки
            $table->string('supplier')->nullable(); // Поставщик
            $table->string('comment')->nullable(); // Поставщик
            $table->decimal('prepayment', 10, 2)->nullable(); // Предоплата
            $table->integer('quantity')->default(1); // Количество
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->foreign('order_id')
                  ->references('id')->on('orders')
                  ->onDelete('cascade'); // удаляем заказ → удаляются позиции
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
