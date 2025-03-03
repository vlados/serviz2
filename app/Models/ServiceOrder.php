<?php

namespace App\Models;

use App\Notifications\ServiceOrderReadyForPickup;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class ServiceOrder extends Model
{
    use HasFactory;
    
    protected static function booted()
    {
        static::creating(function ($serviceOrder) {
            // Generate a unique order number if not set
            if (empty($serviceOrder->order_number)) {
                $serviceOrder->order_number = 'SO-' . now()->format('Ymd') . '-' . random_int(1000, 9999);
            }
        });
        
        static::updated(function ($serviceOrder) {
            // When status changes
            if ($serviceOrder->isDirty('status')) {
                // Send notification when order is ready for pickup (waiting_payment)
                if ($serviceOrder->status === 'waiting_payment') {
                    // Only send if the customer has an email
                    if ($serviceOrder->customer && $serviceOrder->customer->email) {
                        $serviceOrder->customer->notify(new ServiceOrderReadyForPickup($serviceOrder));
                    }
                }
                
                // Automatically mark as completed when paid (if already in waiting_payment status)
                if ($serviceOrder->status === 'waiting_payment' && 
                    $serviceOrder->payment_status === 'paid') {
                    // Schedule completion after a short delay to avoid recursive loop
                    DB::afterCommit(function () use ($serviceOrder) {
                        $serviceOrder->update([
                            'status' => 'completed',
                            'completed_at' => now(),
                        ]);
                    });
                }
            }
            
            // When payment status changes to paid while in waiting_payment status
            if ($serviceOrder->isDirty('payment_status') && 
                $serviceOrder->payment_status === 'paid' && 
                $serviceOrder->status === 'waiting_payment') {
                // Schedule completion after a short delay to avoid recursive loop
                DB::afterCommit(function () use ($serviceOrder) {
                    $serviceOrder->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);
                });
            }
        });
    }
    
    protected $fillable = [
        'order_number', // номер на поръчка
        'customer_id', // идентификатор на клиент
        'scooter_id', // идентификатор на тротинетка
        'received_at', // дата на приемане
        'completed_at', // дата на завършване
        'status', // статус
        'problem_description', // описание на проблема
        'work_performed', // извършена работа
        'labor_hours', // трудоемкост (часове)
        'price', // цена
        'technician_name', // име на техник
        'assigned_to', // възложено на
        'payment_status', // статус на плащане
        'amount_paid', // платена сума
        'payment_method', // метод на плащане
        'payment_date', // дата на плащане
        'payment_notes', // бележки за плащането
    ];

    protected $casts = [
        'received_at' => 'date',
        'completed_at' => 'date',
        'payment_date' => 'date',
        'labor_hours' => 'float',
        'price' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function scooter(): BelongsTo
    {
        return $this->belongsTo(Scooter::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function spareParts(): BelongsToMany
    {
        return $this->belongsToMany(SparePart::class)
            ->withPivot('quantity', 'price_per_unit')
            ->withTimestamps();
    }
    
    /**
     * Get the payments for the service order
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
    
    /**
     * Calculate the remaining amount to be paid
     * 
     * @return float
     */
    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->price - $this->amount_paid);
    }
    
    /**
     * Update payment status based on the amount paid
     * 
     * @return void
     */
    public function updatePaymentStatus(): void
    {
        if ($this->amount_paid <= 0) {
            $this->payment_status = 'unpaid';
        } elseif ($this->amount_paid >= $this->price) {
            $this->payment_status = 'paid';
            if ($this->payment_date === null) {
                $this->payment_date = now();
            }
        } else {
            $this->payment_status = 'partially_paid';
        }
        
        $this->save();
    }
    
    /**
     * Add a payment to the service order
     * 
     * @param float $amount
     * @param string|null $method
     * @param string|null $notes
     * @param string|null $referenceNumber
     * @return void
     */
    public function addPayment(float $amount, ?string $method = null, ?string $notes = null, ?string $referenceNumber = null): void
    {
        $this->amount_paid += $amount;
        
        if ($method) {
            $this->payment_method = $method;
        }
        
        if ($notes) {
            $this->payment_notes = $notes;
        }
        
        $this->payment_date = now();
        $this->updatePaymentStatus();
        
        // Create a payment record in the payments table
        $this->payments()->create([
            'amount' => $amount,
            'payment_method' => $method ?? $this->payment_method,
            'reference_number' => $referenceNumber,
            'payment_date' => now(),
            'notes' => $notes,
            'recorded_by' => auth()->id(),
        ]);
    }
}
