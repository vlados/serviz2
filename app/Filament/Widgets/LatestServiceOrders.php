<?php

namespace App\Filament\Widgets;

use App\Models\ServiceOrder;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestServiceOrders extends BaseWidget
{
    protected static ?string $heading = 'Последни сервизни поръчки';
    
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ServiceOrder::query()
                    ->latest('received_at')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Номер')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Клиент')
                    ->searchable(),
                Tables\Columns\TextColumn::make('scooter.model')
                    ->label('Тротинетка')
                    ->searchable(),
                Tables\Columns\TextColumn::make('received_at')
                    ->label('Получена на')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'В очакване',
                        'in_progress' => 'В процес',
                        'completed' => 'Завършена',
                        'cancelled' => 'Отказана',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('price')
                    ->label('Цена')
                    ->money('BGN')
                    ->sortable(),
            ]);
    }
}