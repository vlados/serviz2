<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SparePart extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name', // име
        'part_number', // номер на част
        'description', // описание
        'stock_quantity', // количество в наличност
        'purchase_price', // покупна цена
        'selling_price', // продажна цена
        'is_active', // активна
    ];

    protected $casts = [
        'stock_quantity' => 'integer',
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function serviceOrders(): BelongsToMany
    {
        return $this->belongsToMany(ServiceOrder::class)
            ->withPivot('quantity', 'price_per_unit')
            ->withTimestamps();
    }
}
