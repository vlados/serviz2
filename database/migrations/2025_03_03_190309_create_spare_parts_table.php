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
        Schema::create('spare_parts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Име
            $table->string('part_number')->unique(); // Номер на част
            $table->text('description')->nullable(); // Описание
            $table->integer('stock_quantity')->default(0); // Количество в наличност
            $table->decimal('purchase_price', 8, 2)->default(0); // Покупна цена
            $table->decimal('selling_price', 8, 2)->default(0); // Продажна цена
            $table->boolean('is_active')->default(true); // Активна
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spare_parts');
    }
};
