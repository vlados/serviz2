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
        Schema::create('scooters', function (Blueprint $table) {
            $table->id();
            $table->string('model'); // Модел
            $table->string('serial_number')->unique(); // Сериен номер
            $table->foreignId('customer_id')->constrained()->onDelete('cascade'); // Идентификатор на клиент
            $table->enum('status', ['in_use', 'in_repair', 'not_working'])->default('in_use'); // Статус: в експлоатация, в ремонт, неработещ
            $table->integer('max_speed')->nullable(); // Максимална скорост в км/ч
            $table->integer('battery_capacity')->nullable(); // Капацитет на батерията в mAh
            $table->float('weight')->nullable(); // Тегло в кг
            $table->text('specifications')->nullable(); // Допълнителни спецификации в JSON или текст
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scooters');
    }
};
