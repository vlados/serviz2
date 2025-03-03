<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
    ];

    protected $casts = [
        'received_at' => 'date',
        'completed_at' => 'date',
        'labor_hours' => 'float',
        'price' => 'decimal:2',
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
}
