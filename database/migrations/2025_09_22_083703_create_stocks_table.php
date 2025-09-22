<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();

            $table->string('name')->comment('Название');
            $table->string('part_make')->nullable()->comment('Бренд');
            $table->string('part_number')->nullable()->comment('Артикул');
            $table->integer('quantity')->default(0)->comment('Количество');
            $table->decimal('volume_step', 10, 2)->nullable()->comment('Объём / Мин.шаг реализаций');
            $table->integer('reserved')->default(0)->comment('Зарезервировано');
            $table->decimal('sell_price', 10, 2)->nullable()->comment('Цена продажи');
            $table->integer('min_stock')->default(0)->comment('Мин. остаток');
            $table->string('warehouse')->nullable()->comment('Склад');
            $table->string('warehouse_address')->nullable()->comment('Адрес склада');
            $table->decimal('purchase_price', 10, 2)->nullable()->comment('Цена закупки');
            $table->string('tags')->nullable()->comment('Теги');
            $table->string('marking')->nullable()->comment('Маркировка');
            $table->string('categories')->nullable()->comment('Категории');
            $table->string('address_code')->nullable()->comment('Адрес - код');
            $table->string('address_name')->nullable()->comment('Адрес - название');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
