<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scooter extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'model', // модел
        'serial_number', // сериен номер
        'customer_id', // идентификатор на клиент
        'status', // статус
        'max_speed', // максимална скорост
        'battery_capacity', // капацитет на батерията
        'weight', // тегло
        'specifications', // спецификации
    ];

    protected $casts = [
        'specifications' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function serviceOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class);
    }
}
