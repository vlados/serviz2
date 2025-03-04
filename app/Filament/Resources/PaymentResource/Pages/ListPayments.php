<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\Payment;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Kainiklas\FilamentScout\Traits\InteractsWithScout;

class ListPayments extends ListRecords
{
    use InteractsWithScout;
    
    protected static string $resource = PaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Всички плащания')
                ->badge(Payment::query()->count()),
                
            'this_month' => Tab::make('Текущ месец')
                ->icon('heroicon-m-calendar')
                ->badge(Payment::query()->whereMonth('payment_date', now()->month)
                    ->whereYear('payment_date', now()->year)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereMonth('payment_date', now()->month)
                    ->whereYear('payment_date', now()->year)),
                
            'last_month' => Tab::make('Предходен месец')
                ->icon('heroicon-m-calendar')
                ->badge(Payment::query()->whereMonth('payment_date', now()->subMonth()->month)
                    ->whereYear('payment_date', now()->subMonth()->year)->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereMonth('payment_date', now()->subMonth()->month)
                    ->whereYear('payment_date', now()->subMonth()->year)),
                
            'cash' => Tab::make('В брой')
                ->icon('heroicon-m-banknotes')
                ->badge(Payment::query()->where('payment_method', 'cash')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('payment_method', 'cash')),
                
            'card' => Tab::make('С карта')
                ->icon('heroicon-m-credit-card')
                ->badge(Payment::query()->where('payment_method', 'card')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('payment_method', 'card')),
                
            'bank_transfer' => Tab::make('Банков превод')
                ->icon('heroicon-m-building-library')
                ->badge(Payment::query()->where('payment_method', 'bank_transfer')->count())
                ->badgeColor('primary')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('payment_method', 'bank_transfer')),
                
            'today' => Tab::make('Днешни')
                ->icon('heroicon-m-calendar-days')
                ->badge(Payment::query()->whereDate('payment_date', today())->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('payment_date', today())),
        ];
    }
    
    public function getDefaultActiveTab(): string | int | null
    {
        return 'this_month';
    }
}
