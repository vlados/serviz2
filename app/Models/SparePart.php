<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Scout\Searchable;

class SparePart extends Model
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
            'name' => (string) $this->name,
            'part_number' => (string) ($this->part_number ?? ''),
            'description' => (string) ($this->description ?? ''),
            'stock_quantity' => (int) ($this->stock_quantity ?? 0),
            'purchase_price' => (float) ($this->purchase_price ?? 0),
            'selling_price' => (float) ($this->selling_price ?? 0),
            'is_active' => (bool) ($this->is_active ?? false),
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
            'name',
            'part_number',
            'description',
        ];
    }
    
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
