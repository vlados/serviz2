<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\ServiceOrder;
use App\Notifications\ServiceOrderReadyForPickup;
use Illuminate\Console\Command;

class TestVonageNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-vonage-notification {customer_id?} {service_order_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Vonage SMS notification sending';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $customerId = $this->argument('customer_id');
        $serviceOrderId = $this->argument('service_order_id');

        if (!$customerId) {
            $customerId = $this->ask('Enter customer ID to notify');
        }
        
        if (!$serviceOrderId) {
            $serviceOrderId = $this->ask('Enter service order ID to use for notification');
        }
        
        $customer = Customer::find($customerId);
        if (!$customer) {
            $this->error("Customer with ID {$customerId} not found");
            return 1;
        }
        
        if (empty($customer->phone)) {
            $this->error("Customer {$customer->name} doesn't have a phone number");
            
            if ($this->confirm('Would you like to add a phone number now?')) {
                $phone = $this->ask('Enter phone number');
                $customer->phone = $phone;
                $customer->save();
                $this->info("Phone number updated: {$phone}");
            } else {
                return 1;
            }
        }
        
        $serviceOrder = ServiceOrder::find($serviceOrderId);
        if (!$serviceOrder) {
            $this->error("Service order with ID {$serviceOrderId} not found");
            return 1;
        }

        $this->info("Sending notification to {$customer->name} about service order #{$serviceOrder->order_number}");
        
        try {
            $customer->notify(new ServiceOrderReadyForPickup($serviceOrder));
            $this->info('Notification sent successfully!');
        } catch (\Exception $e) {
            $this->error("Error sending notification: {$e->getMessage()}");
            return 1;
        }
        
        return 0;
    }
}
