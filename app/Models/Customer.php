<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Scout\Searchable;

class Customer extends Model
{
    use HasFactory, Notifiable, Searchable;
    
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
            'phone' => (string) ($this->phone ?? ''),
            'email' => (string) ($this->email ?? ''),
            'address' => (string) ($this->address ?? ''),
            'notes' => (string) ($this->notes ?? ''),
            'scooters_count' => (int) $this->scooters()->count(),
            'service_orders_count' => (int) $this->serviceOrders()->count(),
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
            'phone',
            'email',
            'address',
            'notes',
        ];
    }
    
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
    
    /**
     * Route notifications for the mail channel.
     *
     * @return string
     */
    public function routeNotificationForMail(): string
    {
        return $this->email;
    }
}
