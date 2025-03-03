<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource;
use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Filament\Resources\ServiceOrderResource;
use App\Models\Payment;
use App\Models\ServiceOrder;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    
    protected static ?string $modelLabel = 'Плащане';
    protected static ?string $pluralModelLabel = 'Плащания';
    protected static ?string $navigationLabel = 'Плащания';
    
    protected static ?string $navigationGroup = 'Финанси и Отчети';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('service_order_id')
                            ->label('Сервизна поръчка')
                            ->options(ServiceOrder::all()->pluck('order_number', 'id'))
                            ->searchable()
                            ->required()
                            ->preload()
                            ->live()
                            ->columnSpanFull()
                            ->afterStateUpdated(function (Get $get, Forms\Set $set, ?string $state) {
                                if ($state) {
                                    $serviceOrder = ServiceOrder::find($state);
                                    if ($serviceOrder) {
                                        $remainingAmount = $serviceOrder->getRemainingAmountAttribute();
                                        $set('amount', $remainingAmount);
                                    }
                                }
                            }),
                            
                        // Service Order Summary Card (visible when a service order is selected)
                        Forms\Components\Section::make('Информация за поръчката')
                            ->hidden(fn (Get $get): bool => empty($get('service_order_id')))
                            ->columnSpanFull()
                            ->columns(3)
                            ->schema([
                                Forms\Components\Placeholder::make('order_number')
                                    ->label('Номер на поръчка')
                                    ->content(function (Get $get): string {
                                        $serviceOrder = ServiceOrder::find($get('service_order_id'));
                                        return $serviceOrder ? $serviceOrder->order_number : '';
                                    }),
                                
                                Forms\Components\Placeholder::make('customer')
                                    ->label('Клиент')
                                    ->content(function (Get $get): string {
                                        $serviceOrder = ServiceOrder::find($get('service_order_id'));
                                        return $serviceOrder && $serviceOrder->customer ? $serviceOrder->customer->name : '';
                                    }),
                                
                                Forms\Components\Placeholder::make('scooter')
                                    ->label('Тротинетка')
                                    ->content(function (Get $get): string {
                                        $serviceOrder = ServiceOrder::find($get('service_order_id'));
                                        return $serviceOrder && $serviceOrder->scooter ? $serviceOrder->scooter->model : '';
                                    }),
                                
                                Forms\Components\Placeholder::make('price')
                                    ->label('Обща цена')
                                    ->content(function (Get $get): string {
                                        $serviceOrder = ServiceOrder::find($get('service_order_id'));
                                        return $serviceOrder ? number_format($serviceOrder->price, 2) . ' лв.' : '';
                                    }),
                                
                                Forms\Components\Placeholder::make('amount_paid')
                                    ->label('Платено до момента')
                                    ->content(function (Get $get): string {
                                        $serviceOrder = ServiceOrder::find($get('service_order_id'));
                                        return $serviceOrder ? number_format($serviceOrder->amount_paid, 2) . ' лв.' : '';
                                    }),
                                
                                Forms\Components\Placeholder::make('remaining')
                                    ->label('Остатък')
                                    ->content(function (Get $get): string {
                                        $serviceOrder = ServiceOrder::find($get('service_order_id'));
                                        return $serviceOrder ? number_format($serviceOrder->getRemainingAmountAttribute(), 2) . ' лв.' : '';
                                    })
                                    ->extraAttributes(['class' => 'font-bold']),
                            ]),
                            
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('amount')
                                    ->label('Сума')
                                    ->prefix('лв.')
                                    ->numeric()
                                    ->required()
                                    ->step(0.01),
                                
                                Forms\Components\Select::make('payment_method')
                                    ->label('Начин на плащане')
                                    ->options([
                                        'cash' => 'В брой',
                                        'card' => 'Карта',
                                        'bank_transfer' => 'Банков превод',
                                        'other' => 'Друго',
                                    ])
                                    ->required()
                                    ->default('cash'),
                                
                                Forms\Components\DateTimePicker::make('payment_date')
                                    ->label('Дата на плащане')
                                    ->required()
                                    ->default(now()),
                            ]),
                        
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('reference_number')
                                    ->label('Референтен номер')
                                    ->helperText('Номер на фактура, банков превод и т.н.')
                                    ->maxLength(255),
                                
                                Forms\Components\Select::make('recorded_by')
                                    ->label('Регистрирано от')
                                    ->relationship('recordedBy', 'name')
                                    ->default(auth()->id())
                                    ->required(),
                            ]),
                        
                        Forms\Components\Textarea::make('notes')
                            ->label('Бележки')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('serviceOrder.order_number')
                    ->label('Поръчка №')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->url(fn (Payment $record): string => ServiceOrderResource::getUrl('edit', ['record' => $record->service_order_id]))
                    ->openUrlInNewTab(),
                
                Tables\Columns\TextColumn::make('serviceOrder.customer.name')
                    ->label('Клиент')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user')
                    ->url(fn (Payment $record): ?string => $record->serviceOrder && $record->serviceOrder->customer 
                        ? CustomerResource::getUrl('edit', ['record' => $record->serviceOrder->customer_id]) 
                        : null)
                    ->openUrlInNewTab(),
                
                Tables\Columns\TextColumn::make('amount')
                    ->label('Сума')
                    ->money('BGN')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('BGN'),
                    ]),
                
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Начин на плащане')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash' => 'В брой',
                        'card' => 'Карта',
                        'bank_transfer' => 'Банков превод',
                        'other' => 'Друго',
                        default => $state,
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'cash' => 'heroicon-o-banknotes',
                        'card' => 'heroicon-o-credit-card',
                        'bank_transfer' => 'heroicon-o-building-library',
                        'other' => 'heroicon-o-document-text',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cash' => 'success',
                        'card' => 'info',
                        'bank_transfer' => 'primary',
                        'other' => 'gray',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Дата на плащане')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),
                
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Референтен номер')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('recordedBy.name')
                    ->label('Регистрирано от')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Създадено на')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновено на')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->actionsPosition(ActionsPosition::BeforeColumns)
            ->filters([
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Начин на плащане')
                    ->options([
                        'cash' => 'В брой',
                        'card' => 'Карта',
                        'bank_transfer' => 'Банков превод',
                        'other' => 'Друго',
                    ]),
                
                Tables\Filters\Filter::make('payment_date')
                    ->label('Период на плащане')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('payment_date_from')
                                    ->label('От дата'),
                                Forms\Components\DatePicker::make('payment_date_until')
                                    ->label('До дата'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['payment_date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('payment_date', '>=', $date),
                            )
                            ->when(
                                $data['payment_date_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('payment_date', '<=', $date),
                            );
                    }),
                
                Tables\Filters\Filter::make('amount')
                    ->label('Сума')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('amount_from')
                                    ->label('От сума')
                                    ->numeric()
                                    ->prefix('лв.'),
                                Forms\Components\TextInput::make('amount_until')
                                    ->label('До сума')
                                    ->numeric()
                                    ->prefix('лв.'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['amount_from'],
                                fn (Builder $query, $amount): Builder => $query->where('amount', '>=', $amount),
                            )
                            ->when(
                                $data['amount_until'],
                                fn (Builder $query, $amount): Builder => $query->where('amount', '<=', $amount),
                            );
                    }),
                
                Tables\Filters\SelectFilter::make('recorded_by')
                    ->label('Регистрирано от')
                    ->relationship('recordedBy', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Преглед')
                        ->icon('heroicon-o-eye'),
                        
                    Tables\Actions\EditAction::make()
                        ->label('Редактиране')
                        ->icon('heroicon-o-pencil'),
                        
                    Tables\Actions\Action::make('openServiceOrder')
                        ->label('Отвори поръчка')
                        ->icon('heroicon-o-wrench-screwdriver')
                        ->color('success')
                        ->url(fn (Payment $record): string => ServiceOrderResource::getUrl('edit', ['record' => $record->service_order_id]))
                        ->openUrlInNewTab(),
                        
                    Tables\Actions\Action::make('openCustomer')
                        ->label('Отвори клиент')
                        ->icon('heroicon-o-user')
                        ->color('primary')
                        ->url(fn (Payment $record): ?string => $record->serviceOrder && $record->serviceOrder->customer 
                            ? CustomerResource::getUrl('edit', ['record' => $record->serviceOrder->customer_id]) 
                            : null)
                        ->hidden(fn (Payment $record): bool => !$record->serviceOrder || !$record->serviceOrder->customer)
                        ->openUrlInNewTab(),
                        
                    Tables\Actions\DeleteAction::make()
                        ->label('Изтриване')
                        ->icon('heroicon-o-trash'),
                ])
                ->tooltip('Действия')
                ->button()
                ->color('gray')
                ->dropdownPlacement('bottom-start')
                ->label('Действия')
                ->size('xs'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('payment_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
