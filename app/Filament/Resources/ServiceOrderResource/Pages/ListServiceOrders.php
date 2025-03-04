<?php

namespace App\Filament\Resources\ServiceOrderResource\Pages;

use App\Filament\Resources\ServiceOrderResource;
use App\Models\ServiceOrder;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Kainiklas\FilamentScout\Traits\InteractsWithScout;

class ListServiceOrders extends ListRecords
{
    use InteractsWithScout;
    
    protected static string $resource = ServiceOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Всички поръчки')
                ->badge(ServiceOrder::query()->count()),
                
            'pending' => Tab::make('В очакване')
                ->icon('heroicon-m-clock')
                ->badge(ServiceOrder::query()->where('status', 'pending')->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending')),
                
            'in_progress' => Tab::make('В процес')
                ->icon('heroicon-m-play')
                ->badge(ServiceOrder::query()->where('status', 'in_progress')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'in_progress')),
                
            'waiting_payment' => Tab::make('Чака плащане')
                ->icon('heroicon-m-banknotes')
                ->badge(ServiceOrder::query()->where('status', 'waiting_payment')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'waiting_payment')),
                
            'completed' => Tab::make('Завършени')
                ->icon('heroicon-m-check-circle')
                ->badge(ServiceOrder::query()->where('status', 'completed')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'completed')),
                
            'cancelled' => Tab::make('Отказани')
                ->icon('heroicon-m-x-circle')
                ->badge(ServiceOrder::query()->where('status', 'cancelled')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'cancelled')),
                
            'unpaid' => Tab::make('Неплатени')
                ->icon('heroicon-m-exclamation-circle')
                ->badge(ServiceOrder::query()->where('payment_status', 'unpaid')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('payment_status', 'unpaid')),
                
            'today' => Tab::make('Днешни')
                ->icon('heroicon-m-calendar')
                ->badge(ServiceOrder::query()->whereDate('received_at', today())->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('received_at', today())),
        ];
    }
    
    public function getDefaultActiveTab(): string | int | null
    {
        return 'all';
    }
}
