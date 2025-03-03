<?php

namespace App\Filament\Resources\ScooterResource\Pages;

use App\Filament\Resources\ScooterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListScooters extends ListRecords
{
    protected static string $resource = ScooterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
