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
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // Номер на поръчка
            $table->foreignId('customer_id')->constrained()->onDelete('cascade'); // Идентификатор на клиент
            $table->foreignId('scooter_id')->constrained()->onDelete('cascade'); // Идентификатор на тротинетка
            $table->date('received_at'); // Дата на приемане
            $table->date('completed_at')->nullable(); // Дата на завършване
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending'); // Статус: в очакване, в процес, завършена, отказана
            $table->text('problem_description'); // Описание на проблема
            $table->text('work_performed')->nullable(); // Извършена работа
            $table->float('labor_hours')->default(0); // Трудоемкост (часове)
            $table->decimal('price', 8, 2)->default(0); // Цена
            $table->string('technician_name')->nullable(); // Име на техник
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete(); // Възложено на
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
