<?php

namespace App\Filament\Resources\ServiceOrderResource\Pages;

use App\Filament\Resources\ServiceOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateServiceOrder extends CreateRecord
{
    protected static string $resource = ServiceOrderResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate order number if not set
        if (!isset($data['order_number']) || empty($data['order_number'])) {
            $data['order_number'] = 'SO-' . now()->format('Ymd') . '-' . random_int(1000, 9999);
        }
        
        return $data;
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getPrefilledData(): array
    {
        $data = [];
        
        // Check for query parameters
        $customerId = request()->query('customer_id');
        $scooterId = request()->query('scooter_id');
        
        if ($customerId) {
            $data['customer_id'] = $customerId;
        }
        
        if ($scooterId) {
            $data['scooter_id'] = $scooterId;
        }
        
        return $data;
    }
}
