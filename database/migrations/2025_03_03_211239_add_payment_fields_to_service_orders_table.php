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
        Schema::table('service_orders', function (Blueprint $table) {
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid'])->default('unpaid')->after('price');
            $table->decimal('amount_paid', 8, 2)->default(0)->after('payment_status');
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'other'])->nullable()->after('amount_paid');
            $table->date('payment_date')->nullable()->after('payment_method');
            $table->text('payment_notes')->nullable()->after('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_status',
                'amount_paid',
                'payment_method',
                'payment_date',
                'payment_notes',
            ]);
        });
    }
};
