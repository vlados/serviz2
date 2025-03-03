<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get service orders that are paid or partially paid
        $serviceOrders = \App\Models\ServiceOrder::whereIn('payment_status', ['paid', 'partially_paid'])->get();
        
        // For each service order, create at least one payment record
        foreach ($serviceOrders as $order) {
            // For partially paid orders, create a payment for a portion of the price
            if ($order->payment_status === 'partially_paid') {
                \App\Models\Payment::factory()->create([
                    'service_order_id' => $order->id,
                    'amount' => $order->amount_paid,
                    'payment_method' => $order->payment_method,
                    'payment_date' => $order->payment_date ?? now()->subDays(rand(1, 30)),
                    'recorded_by' => \App\Models\User::inRandomOrder()->first()->id,
                ]);
            } 
            // For fully paid orders, create one or more payments that sum to the price
            else {
                // Decide if we want to split payment into multiple transactions
                $numPayments = rand(1, 3);
                if ($numPayments === 1) {
                    \App\Models\Payment::factory()->create([
                        'service_order_id' => $order->id,
                        'amount' => $order->price,
                        'payment_method' => $order->payment_method,
                        'payment_date' => $order->payment_date ?? now()->subDays(rand(1, 30)),
                        'recorded_by' => \App\Models\User::inRandomOrder()->first()->id,
                    ]);
                } else {
                    // Split into multiple payments
                    $totalPaid = 0;
                    $remainingAmount = $order->price;
                    
                    for ($i = 0; $i < $numPayments - 1; $i++) {
                        $paymentAmount = round($remainingAmount * (rand(20, 70) / 100), 2);
                        $totalPaid += $paymentAmount;
                        $remainingAmount -= $paymentAmount;
                        
                        \App\Models\Payment::factory()->create([
                            'service_order_id' => $order->id,
                            'amount' => $paymentAmount,
                            'payment_method' => $order->payment_method,
                            'payment_date' => $order->payment_date 
                                ? $order->payment_date->subDays(rand(1, 7)) 
                                : now()->subDays(rand(5, 30)),
                            'recorded_by' => \App\Models\User::inRandomOrder()->first()->id,
                        ]);
                    }
                    
                    // Add final payment to match the total price exactly
                    \App\Models\Payment::factory()->create([
                        'service_order_id' => $order->id,
                        'amount' => $order->price - $totalPaid,
                        'payment_method' => $order->payment_method,
                        'payment_date' => $order->payment_date ?? now()->subDays(rand(1, 5)),
                        'recorded_by' => \App\Models\User::inRandomOrder()->first()->id,
                    ]);
                }
            }
        }
    }
}
