<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name', // име
        'phone', // телефон
        'email', // имейл
        'address', // адрес
        'notes', // бележки
    ];

    public function scooters(): HasMany
    {
        return $this->hasMany(Scooter::class);
    }

    public function serviceOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class);
    }
}
