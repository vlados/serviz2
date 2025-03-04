<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class Payment extends Model
{
    use HasFactory, Searchable;
    
    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        // Create a simplified searchable array with only what we need
        $searchable = [
            'id' => (string) $this->id,
            'amount' => (float) ($this->amount ?? 0),
            'payment_method' => (string) ($this->payment_method ?? ''),
            'reference_number' => (string) ($this->reference_number ?? ''),
            'notes' => (string) ($this->notes ?? ''),
            'service_order_number' => (string) ($this->serviceOrder->order_number ?? ''),
            'customer_name' => (string) ($this->serviceOrder->customer->name ?? ''),
            'payment_date' => isset($this->payment_date) ? (int) (strtotime($this->payment_date) * 1000) : null,
            'created_at' => (int) (strtotime($this->created_at) * 1000),
        ];
        
        return $searchable;
    }
    
    /**
     * The attributes that should be indexed.
     */
    public function typesenseQueryBy(): array
    {
        return [
            'payment_method',
            'reference_number',
            'notes',
            'service_order_number',
            'customer_name',
        ];
    }
    
    protected $fillable = [
        'service_order_id',
        'amount',
        'payment_method',
        'reference_number',
        'payment_date',
        'notes',
        'recorded_by',
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
    ];
    
    /**
     * Get the service order that owns the payment
     */
    public function serviceOrder(): BelongsTo
    {
        return $this->belongsTo(ServiceOrder::class);
    }
    
    /**
     * Get the user who recorded the payment
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
