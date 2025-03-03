<?php

namespace App\Providers\Filament;

use App\Models\Customer;
use App\Models\Scooter;
use App\Models\ServiceOrder;
use App\Models\SparePart;
use Filament\GlobalSearch\GlobalSearchProvider as BaseGlobalSearchProvider;
use Filament\GlobalSearch\GlobalSearchResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class GlobalSearchProvider extends BaseGlobalSearchProvider
{
    /**
     * Enhanced global search provider that combines results from different models
     * and allows searching for any term across multiple fields in related tables.
     */
    public function getResults(string $query): Collection
    {
        $results = collect();
        
        // Quick search query builder for generic search patterns
        $searchTerm = '%' . $query . '%';
        
        // Search Customers
        $customers = Customer::where('name', 'like', $searchTerm)
            ->orWhere('phone', 'like', $searchTerm)
            ->orWhere('email', 'like', $searchTerm)
            ->orWhere('address', 'like', $searchTerm)
            ->limit(10)
            ->get();
            
        foreach ($customers as $customer) {
            $results->push(
                GlobalSearchResult::make()
                    ->title($customer->name)
                    ->description('Клиент')
                    ->icon('heroicon-o-user')
                    ->details([
                        'Телефон' => $customer->phone,
                        'Имейл' => $customer->email,
                    ])
                    ->url(route('filament.admin.resources.customers.edit', ['record' => $customer]))
            );
        }
        
        // Search Scooters - including by customer name
        $scooters = Scooter::where('model', 'like', $searchTerm)
            ->orWhere('serial_number', 'like', $searchTerm)
            ->orWhereHas('customer', function (Builder $query) use ($searchTerm) {
                $query->where('name', 'like', $searchTerm);
            })
            ->limit(10)
            ->get();
            
        foreach ($scooters as $scooter) {
            $results->push(
                GlobalSearchResult::make()
                    ->title($scooter->model . ' (' . $scooter->serial_number . ')')
                    ->description('Тротинетка')
                    ->icon('heroicon-o-bolt')
                    ->details([
                        'Клиент' => $scooter->customer->name,
                        'Статус' => match($scooter->status) {
                            'in_use' => 'В експлоатация',
                            'in_repair' => 'В ремонт',
                            'not_working' => 'Неработеща',
                            default => $scooter->status,
                        },
                    ])
                    ->url(route('filament.admin.resources.scooters.edit', ['record' => $scooter]))
            );
        }
        
        // Search Service Orders - including by order number, description, customer, or scooter
        $serviceOrders = ServiceOrder::where('order_number', 'like', $searchTerm)
            ->orWhere('problem_description', 'like', $searchTerm)
            ->orWhereHas('customer', function (Builder $query) use ($searchTerm) {
                $query->where('name', 'like', $searchTerm);
            })
            ->orWhereHas('scooter', function (Builder $query) use ($searchTerm) {
                $query->where('model', 'like', $searchTerm)
                    ->orWhere('serial_number', 'like', $searchTerm);
            })
            ->limit(5)
            ->get();
            
        foreach ($serviceOrders as $serviceOrder) {
            $results->push(
                GlobalSearchResult::make()
                    ->title('Поръчка #' . $serviceOrder->order_number)
                    ->description('Сервизна поръчка')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->details([
                        'Клиент' => $serviceOrder->customer->name,
                        'Тротинетка' => $serviceOrder->scooter->model,
                        'Статус' => match($serviceOrder->status) {
                            'pending' => 'В очакване',
                            'in_progress' => 'В процес',
                            'completed' => 'Завършена',
                            'cancelled' => 'Отказана',
                            default => $serviceOrder->status,
                        },
                    ])
                    ->url(route('filament.admin.resources.service-orders.edit', ['record' => $serviceOrder]))
            );
        }
        
        // Search Spare Parts
        $spareParts = SparePart::where('name', 'like', $searchTerm)
            ->orWhere('part_number', 'like', $searchTerm)
            ->orWhere('description', 'like', $searchTerm)
            ->limit(5)
            ->get();
            
        foreach ($spareParts as $sparePart) {
            $results->push(
                GlobalSearchResult::make()
                    ->title($sparePart->name)
                    ->description('Резервна част')
                    ->icon('heroicon-o-cog')
                    ->details([
                        'Номер на част' => $sparePart->part_number,
                        'Наличност' => $sparePart->stock_quantity . ' бр.',
                        'Цена' => number_format($sparePart->selling_price, 2) . ' лв.',
                    ])
                    ->url(route('filament.admin.resources.spare-parts.edit', ['record' => $sparePart]))
            );
        }
        
        return $results;
    }
}