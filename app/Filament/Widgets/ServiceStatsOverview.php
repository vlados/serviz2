<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Scooter;
use App\Models\ServiceOrder;
use App\Models\SparePart;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ServiceStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Общо клиенти', Customer::count())
                ->description('Общ брой регистрирани клиенти')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
                
            Stat::make('Тротинетки', Scooter::count())
                ->description('Общ брой регистрирани тротинетки')
                ->descriptionIcon('heroicon-m-bolt')
                ->color('warning'),
                
            Stat::make('Чакащи поръчки', ServiceOrder::where('status', 'pending')->count())
                ->description('Поръчки чакащи обработка')
                ->descriptionIcon('heroicon-m-wrench-screwdriver')
                ->color('danger'),
                
            Stat::make('В процес', ServiceOrder::where('status', 'in_progress')->count())
                ->description('Поръчки в процес на изпълнение')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning'),
                
            Stat::make('Завършени поръчки', ServiceOrder::where('status', 'completed')->count())
                ->description('Завършени сервизни поръчки')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
                
            Stat::make('Липсващи части', SparePart::where('stock_quantity', '<', 5)->count())
                ->description('Части с ниска наличност')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}