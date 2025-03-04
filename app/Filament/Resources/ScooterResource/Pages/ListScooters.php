<?php

namespace App\Filament\Resources\ScooterResource\Pages;

use App\Filament\Resources\ScooterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Kainiklas\FilamentScout\Traits\InteractsWithScout;

class ListScooters extends ListRecords
{
    use InteractsWithScout;
    
    protected static string $resource = ScooterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
