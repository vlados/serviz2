<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;
    
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
