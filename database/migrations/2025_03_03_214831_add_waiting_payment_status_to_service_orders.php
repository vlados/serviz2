<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For PostgreSQL, we need to update the check constraint
        if (DB::connection()->getDriverName() === 'pgsql') {
            // Drop the existing constraint
            DB::statement('ALTER TABLE service_orders DROP CONSTRAINT IF EXISTS service_orders_status_check');
            
            // Add the new constraint with the waiting_payment option
            DB::statement("ALTER TABLE service_orders ADD CONSTRAINT service_orders_status_check 
                CHECK (status::text = ANY (ARRAY['pending'::character varying, 'in_progress'::character varying, 
                'waiting_payment'::character varying, 'completed'::character varying, 
                'cancelled'::character varying]::text[]))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // For PostgreSQL, revert to the original constraint
        if (DB::connection()->getDriverName() === 'pgsql') {
            // Drop the updated constraint
            DB::statement('ALTER TABLE service_orders DROP CONSTRAINT IF EXISTS service_orders_status_check');
            
            // Add back the original constraint without waiting_payment
            DB::statement("ALTER TABLE service_orders ADD CONSTRAINT service_orders_status_check 
                CHECK (status::text = ANY (ARRAY['pending'::character varying, 'in_progress'::character varying, 
                'completed'::character varying, 'cancelled'::character varying]::text[]))");
        }
    }
};