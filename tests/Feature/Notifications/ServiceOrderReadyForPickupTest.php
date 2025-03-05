<?php

namespace Tests\Feature\Notifications;

use App\Models\Customer;
use App\Models\Scooter;
use App\Models\ServiceOrder;
use App\Notifications\ServiceOrderReadyForPickup;
use App\Notifications\VonageSmsChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ServiceOrderReadyForPickupTest extends TestCase
{
    use RefreshDatabase;

    protected Customer $customer;
    protected Scooter $scooter;
    protected ServiceOrder $serviceOrder;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable Scout/Typesense during tests
        Config::set('scout.driver', null);
        
        // Create required roles and permissions for the test
        $this->seedRolesAndPermissions();

        // Create a customer with an email
        $this->customer = Customer::factory()->create([
            'name' => 'Test Customer',
            'email' => 'test@example.com',
            'phone' => '0888123456',
        ]);

        // Create a scooter for the customer
        $this->scooter = Scooter::factory()->create([
            'customer_id' => $this->customer->id,
            'model' => 'Test Scooter Model',
            'max_speed' => 25, // Adding this field to avoid the Typesense error
        ]);

        // Create a service order for the scooter with minimal data to avoid factory issues
        $this->serviceOrder = ServiceOrder::create([
            'customer_id' => $this->customer->id,
            'scooter_id' => $this->scooter->id,
            'order_number' => 'TEST-123',
            'price' => 100.00,
            'problem_description' => 'Test problem', // Adding the required field
            'work_performed' => 'Test work performed',
            'status' => 'in_progress',
            'received_at' => now(),
            'labor_hours' => 1.0, // Adding the required field
        ]);
    }
    
    /**
     * Seed roles and permissions needed for testing
     */
    protected function seedRolesAndPermissions(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // Create technician role for tests
        Role::create(['name' => 'technician']);
        Role::create(['name' => 'техник']);
    }

    public function testNotificationCanBeCreated(): void
    {
        $notification = new ServiceOrderReadyForPickup($this->serviceOrder);
        
        $this->assertInstanceOf(ServiceOrderReadyForPickup::class, $notification);
        $this->assertSame($this->serviceOrder, $notification->serviceOrder);
    }

    public function testNotificationSentViaCorrectChannelsToCustomerWithPhone(): void
    {
        Notification::fake();

        // Send notification
        $this->customer->notify(new ServiceOrderReadyForPickup($this->serviceOrder));

        // Assert notification was sent to the correct channels
        Notification::assertSentTo(
            $this->customer,
            ServiceOrderReadyForPickup::class,
            function ($notification, $channels) {
                return in_array('mail', $channels) && 
                       in_array(VonageSmsChannel::class, $channels);
            }
        );
    }

    public function testNotificationOnlySentViaEmailToCustomerWithoutPhone(): void
    {
        Notification::fake();

        // Update customer to have no phone
        $this->customer->update(['phone' => null]);

        // Send notification
        $this->customer->notify(new ServiceOrderReadyForPickup($this->serviceOrder));

        // Assert notification was only sent via email
        Notification::assertSentTo(
            $this->customer,
            ServiceOrderReadyForPickup::class,
            function ($notification, $channels) {
                return in_array('mail', $channels) && 
                       !in_array(VonageSmsChannel::class, $channels);
            }
        );
    }

    public function testEmailNotificationHasCorrectContent(): void
    {
        Notification::fake();

        // Send notification
        $this->customer->notify(new ServiceOrderReadyForPickup($this->serviceOrder));

        // Assert email content
        Notification::assertSentTo(
            $this->customer,
            ServiceOrderReadyForPickup::class,
            function (ServiceOrderReadyForPickup $notification) {
                $mailData = $notification->toMail($this->customer);
                
                return $mailData->subject === 'Вашата тротинетка е готова за вземане' &&
                       str_contains($mailData->greeting, 'Здравейте, Test Customer!') &&
                       str_contains($mailData->introLines[0], 'Вашата тротинетка Test Scooter Model е готова за вземане') &&
                       str_contains($mailData->introLines[2], 'Номер на поръчка: TEST-123') &&
                       str_contains($mailData->introLines[3], 'Извършена работа: Test work performed') &&
                       str_contains($mailData->introLines[4], 'Обща сума за плащане: 100.00 лв.');
            }
        );
    }

    public function testSmsNotificationHasCorrectContent(): void
    {
        // Get the SMS content
        $notification = new ServiceOrderReadyForPickup($this->serviceOrder);
        $smsContent = $notification->toVonage($this->customer);
        
        $this->assertStringContainsString('Здравейте, Test Customer!', $smsContent);
        $this->assertStringContainsString('Вашата тротинетка Test Scooter Model е готова за вземане', $smsContent);
        $this->assertStringContainsString('Номер на поръчка: TEST-123', $smsContent);
        $this->assertStringContainsString('Обща сума: 100.00 лв.', $smsContent);
    }

    public function testArrayRepresentationHasCorrectStructure(): void
    {
        $notification = new ServiceOrderReadyForPickup($this->serviceOrder);
        $array = $notification->toArray($this->customer);
        
        $this->assertIsArray($array);
        $this->assertArrayHasKey('service_order_id', $array);
        $this->assertArrayHasKey('order_number', $array);
        $this->assertArrayHasKey('scooter_model', $array);
        $this->assertArrayHasKey('completed_at', $array);
        $this->assertEquals($this->serviceOrder->id, $array['service_order_id']);
        $this->assertEquals('TEST-123', $array['order_number']);
        $this->assertEquals('Test Scooter Model', $array['scooter_model']);
    }

    public function testServiceOrderStatusChangeTriggerNotification(): void
    {
        Notification::fake();
        
        // Update service order status to trigger notification
        $this->serviceOrder->update(['status' => 'waiting_payment']);
        
        // Assert notification was sent
        Notification::assertSentTo(
            $this->customer,
            ServiceOrderReadyForPickup::class
        );
    }
}