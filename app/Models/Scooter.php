<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Scooter extends Model
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
            'model' => (string) $this->model,
            'serial_number' => (string) ($this->serial_number ?? ''),
            'customer_name' => (string) ($this->customer->name ?? ''),
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
            'model',
            'serial_number',
            'customer_name',
        ];
    }
    
    /**
     * Configure the typesense query parameters
     */
    public function typesenseQueryParameters(): array
    {
        return [
            'query_by' => 'model,serial_number,customer_name',
            'enable_transliteration' => true,
            'prefix' => true,
            'infix' => true,
        ];
    }
    
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
