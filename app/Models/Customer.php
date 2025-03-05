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
        // Get original name
        $nameOriginal = (string) $this->name;
        
        // Detect if the name contains Cyrillic characters
        $containsCyrillic = $this->isCyrillic($nameOriginal);
        
        // Create both transliterators
        $cyrillicToLatin = \Transliterator::create('Cyrillic-Latin');
        $latinToCyrillic = \Transliterator::create('Latin-Cyrillic');
        
        // Create both versions of the name
        $nameLatin = $containsCyrillic ? $cyrillicToLatin->transliterate($nameOriginal) : $nameOriginal;
        $nameBg = !$containsCyrillic ? $latinToCyrillic->transliterate($nameOriginal) : $nameOriginal;
        
        // Create a simplified searchable array with only what we need
        $searchable = [
            'id' => (string) $this->id,
            'name' => $nameOriginal, // Original name as entered
            'name_latin' => $nameLatin, // Latin transliteration 
            'name_bg' => $nameBg, // Bulgarian/Cyrillic version
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
     * Detect if a string contains any Cyrillic characters
     *
     * @param string $text
     * @return bool
     */
    protected function isCyrillic(string $text): bool
    {
        // Check if string contains any Cyrillic characters
        return (bool) preg_match('/[\p{Cyrillic}]/u', $text);
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
    
    /**
     * Configure the typesense query parameters
     */
    public function typesenseQueryParameters(): array
    {
        return [
            'query_by' => 'name,name_latin,name_bg,phone,email,address,notes',
            'prefix' => true,
            'infix' => true,
            'typo_tokens_threshold' => 1
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
    
    /**
     * Route notifications for the Vonage SMS channel.
     *
     * @param \Illuminate\Notifications\Notification $notification
     * @return string|null
     */
    public function routeNotificationForVonage($notification): ?string
    {
        if (empty($this->phone)) {
            return null;
        }
        
        return $this->phone;
    }
}
