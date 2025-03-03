<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceOrderResource\Pages;
use App\Filament\Resources\ServiceOrderResource\RelationManagers;
use App\Models\ServiceOrder;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ServiceOrderResource extends Resource
{
    protected static ?string $model = ServiceOrder::class;
    
    protected static ?string $modelLabel = 'Сервизна Поръчка';
    protected static ?string $pluralModelLabel = 'Сервизни Поръчки';

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    
    protected static ?string $navigationGroup = 'Управление на Сервиза';
    
    protected static ?int $navigationSort = 1;
    
    public static function getGloballySearchableAttributes(): array
    {
        return [
            'order_number',
            'customer.name',
            'scooter.model',
            'scooter.serial_number',
            'problem_description'
        ];
    }
    
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return 'Поръчка #' . $record->order_number;
    }
    
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Клиент' => $record->customer->name,
            'Тротинетка' => $record->scooter->model,
            'Статус' => match($record->status) {
                'pending' => 'В очакване',
                'in_progress' => 'В процес',
                'completed' => 'Завършена',
                'cancelled' => 'Отказана',
                default => $record->status,
            },
            'Дата' => $record->received_at->format('d.m.Y'),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Сервизна поръчка')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Основна информация')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->schema([
                                Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('order_number')
                                            ->label('Номер на поръчка')
                                            ->required()
                                            ->maxLength(255)
                                            ->default(fn () => 'SO-' . now()->format('Ymd') . '-' . random_int(1000, 9999))
                                            ->disabled(),
                                        
                                        Forms\Components\Group::make()
                                            ->schema([
                                                Forms\Components\Select::make('customer_id')
                                                    ->label('Клиент')
                                                    ->relationship('customer', 'name')
                                                    ->required()
                                                    ->searchable()
                                                    ->preload()
                                                    ->live()
                                                    ->afterStateUpdated(fn (Select $component) => $component
                                                        ->getContainer()
                                                        ->getComponent('scooterSelector')
                                                        ->getChildComponentContainer()
                                                        ->fill())
                                                    ->createOptionForm([
                                                        Forms\Components\TextInput::make('name')
                                                            ->label('Име')
                                                            ->required()
                                                            ->maxLength(255),
                                                        Forms\Components\TextInput::make('phone')
                                                            ->label('Телефон')
                                                            ->tel()
                                                            ->maxLength(255),
                                                        Forms\Components\TextInput::make('email')
                                                            ->label('Имейл')
                                                            ->email()
                                                            ->maxLength(255),
                                                    ])
                                                    ->columnSpan(2),
                                                
                                                Forms\Components\Group::make()
                                                    ->schema(fn (Get $get) => [
                                                        Forms\Components\Select::make('scooter_id')
                                                            ->label('Тротинетка')
                                                            ->options(function (Get $get) {
                                                                $customerId = $get('customer_id');
                                                                
                                                                if (!$customerId) {
                                                                    return [];
                                                                }
                                                                
                                                                return \App\Models\Scooter::query()
                                                                    ->where('customer_id', $customerId)
                                                                    ->pluck('model', 'id')
                                                                    ->toArray();
                                                            })
                                                            ->required()
                                                            ->searchable()
                                                            ->preload()
                                                            ->createOptionForm([
                                                                Forms\Components\TextInput::make('model')
                                                                    ->label('Модел')
                                                                    ->required()
                                                                    ->maxLength(255),
                                                                Forms\Components\TextInput::make('serial_number')
                                                                    ->label('Сериен номер')
                                                                    ->required()
                                                                    ->maxLength(255),
                                                                Forms\Components\Hidden::make('customer_id')
                                                                    ->default(function (Get $get) {
                                                                        return $get('../../customer_id');
                                                                    }),
                                                            ])
                                                    ])
                                                    ->key('scooterSelector')
                                                    ->columnSpan(2),
                                            ])->columns(4),
                                        
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\DatePicker::make('received_at')
                                                    ->label('Дата на приемане')
                                                    ->required()
                                                    ->default(now()),
                                                
                                                Forms\Components\DatePicker::make('completed_at')
                                                    ->label('Дата на завършване'),
                                                
                                                Forms\Components\Select::make('status')
                                                    ->label('Статус')
                                                    ->options([
                                                        'pending' => 'В очакване',
                                                        'in_progress' => 'В процес',
                                                        'completed' => 'Завършена',
                                                        'cancelled' => 'Отказана',
                                                    ])
                                                    ->required()
                                                    ->default('pending')
                                                    ->live()
                                                    ->dehydrated()
                                                    ->selectablePlaceholder(false)
                                                    ->suffixIcon('heroicon-o-clipboard-document-check'),
                                            ]),
                                        
                                        Forms\Components\RichEditor::make('problem_description')
                                            ->label('Описание на проблема')
                                            ->required()
                                            ->placeholder('Подробно описание на докладвания проблем...')
                                            ->columnSpanFull()
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'bulletList',
                                                'orderedList',
                                            ]),
                                    ])->columns(2),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make('Детайли на обслужването')
                            ->icon('heroicon-o-wrench')
                            ->schema([
                                Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\RichEditor::make('work_performed')
                                            ->label('Извършена работа')
                                            ->placeholder('Опишете извършената работа и ремонти...')
                                            ->columnSpanFull()
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'bulletList',
                                                'orderedList',
                                            ]),
                                        
                                        Forms\Components\Grid::make(3)
                                            ->schema([
                                                Forms\Components\TextInput::make('labor_hours')
                                                    ->label('Трудоемкост (часове)')
                                                    ->numeric()
                                                    ->default(0)
                                                    ->minValue(0)
                                                    ->step(0.5)
                                                    ->suffixIcon('heroicon-o-clock'),
                                                
                                                Forms\Components\TextInput::make('price')
                                                    ->label('Цена')
                                                    ->numeric()
                                                    ->prefix('лв')
                                                    ->default(0)
                                                    ->suffixIcon('heroicon-o-currency-euro'),
                                                
                                                Forms\Components\Select::make('assigned_to')
                                                    ->label('Възложено на техник')
                                                    ->relationship('technician', 'name')
                                                    ->searchable()
                                                    ->suffixIcon('heroicon-o-user'),
                                            ]),
                                    ]),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Номер')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->tooltip('Номер на сервизна поръчка')
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'В очакване',
                        'in_progress' => 'В процес',
                        'completed' => 'Завършена',
                        'cancelled' => 'Отказана',
                        default => $state,
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'in_progress' => 'heroicon-o-play',
                        'completed' => 'heroicon-o-check-circle', 
                        'cancelled' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Клиент')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->customer->phone ?? '')
                    ->icon('heroicon-o-user'),
                
                Tables\Columns\TextColumn::make('scooter.model')
                    ->label('Тротинетка')
                    ->searchable()
                    ->description(fn ($record) => $record->scooter->serial_number ?? 'Без сериен номер')
                    ->icon('heroicon-o-truck'),
                
                Tables\Columns\TextColumn::make('received_at')
                    ->label('Получена на')
                    ->date('d.m.Y')
                    ->sortable()
                    ->icon('heroicon-o-calendar'),
                
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Завършена на')
                    ->date('d.m.Y')
                    ->sortable()
                    ->toggleable()
                    ->icon('heroicon-o-flag'),
                    
                Tables\Columns\TextColumn::make('price')
                    ->label('Цена')
                    ->money('BGN')
                    ->sortable()
                    ->icon('heroicon-o-currency-euro'),
                
                Tables\Columns\TextColumn::make('technician.name')
                    ->label('Техник')
                    ->toggleable()
                    ->icon('heroicon-o-user-circle'),
                
                Tables\Columns\TextColumn::make('labor_hours')
                    ->label('Часове')
                    ->numeric(2)
                    ->sortable()
                    ->toggleable()
                    ->suffix(' ч.')
                    ->alignRight()
                    ->icon('heroicon-o-clock'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->multiple()
                    ->options([
                        'pending' => 'В очакване',
                        'in_progress' => 'В процес',
                        'completed' => 'Завършена',
                        'cancelled' => 'Отказана',
                    ]),
                
                Tables\Filters\Filter::make('received_at')
                    ->label('Период на приемане')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('received_from')
                                    ->label('От дата'),
                                Forms\Components\DatePicker::make('received_until')
                                    ->label('До дата'),
                            ]),
                    ])
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        
                        if ($data['received_from'] ?? null) {
                            $indicators['received_from'] = 'От дата: ' . \Carbon\Carbon::parse($data['received_from'])->format('d.m.Y');
                        }
                        
                        if ($data['received_until'] ?? null) {
                            $indicators['received_until'] = 'До дата: ' . \Carbon\Carbon::parse($data['received_until'])->format('d.m.Y');
                        }
                        
                        return $indicators;
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['received_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('received_at', '>=', $date),
                            )
                            ->when(
                                $data['received_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('received_at', '<=', $date),
                            );
                    }),
                    
                Tables\Filters\SelectFilter::make('technician')
                    ->label('Техник')
                    ->relationship('technician', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->filtersFormColumns(3)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Преглед')
                        ->icon('heroicon-o-eye'),
                    
                    Tables\Actions\EditAction::make()
                        ->label('Редактиране')
                        ->icon('heroicon-o-pencil'),
                    
                    Tables\Actions\Action::make('printLabel')
                        ->label('Печат на етикет')
                        ->icon('heroicon-o-printer')
                        ->color('success')
                        ->url(fn (ServiceOrder $record): string => route('service-orders.print-label', $record))
                        ->openUrlInNewTab(),
                    
                    Tables\Actions\Action::make('complete')
                        ->label('Маркирай като завършена')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (ServiceOrder $record): bool => $record->status !== 'completed')
                        ->action(function (ServiceOrder $record): void {
                            $record->update([
                                'status' => 'completed',
                                'completed_at' => now(),
                            ]);
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Маркирай като завършена')
                        ->modalDescription('Сигурни ли сте, че искате да маркирате тази поръчка като завършена?')
                        ->modalSubmitActionLabel('Да, завърши поръчката'),
                    
                    Tables\Actions\Action::make('cancel')
                        ->label('Отмени поръчката')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn (ServiceOrder $record): bool => $record->status !== 'cancelled')
                        ->action(function (ServiceOrder $record): void {
                            $record->update([
                                'status' => 'cancelled',
                            ]);
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Отмени поръчката')
                        ->modalDescription('Сигурни ли сте, че искате да отмените тази поръчка?')
                        ->modalSubmitActionLabel('Да, отмени поръчката'),
                ])
                ->tooltip('Действия')
                ->iconButton()
                ->icon('heroicon-o-ellipsis-vertical'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Изтриване на избраните')
                        ->icon('heroicon-o-trash'),
                        
                    Tables\Actions\BulkAction::make('printLabels')
                        ->label('Печат на етикети')
                        ->icon('heroicon-o-printer')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            // Generate a unique identifier for this batch
                            $batchId = Str::random(10);
                            
                            // Store the record IDs in the session
                            session()->put('print_batch_' . $batchId, $records->pluck('id')->toArray());
                            
                            // Redirect to the bulk print page
                            redirect()->route('service-orders.print-bulk-labels', ['batchId' => $batchId]);
                        }),
                        
                    Tables\Actions\BulkAction::make('markAsCompleted')
                        ->label('Маркирай като завършени')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each(function ($record) {
                                if ($record->status !== 'completed') {
                                    $record->update([
                                        'status' => 'completed',
                                        'completed_at' => now(),
                                    ]);
                                }
                            });
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Маркирай избраните като завършени')
                        ->modalDescription('Сигурни ли сте, че искате да маркирате избраните поръчки като завършени?')
                        ->modalSubmitActionLabel('Да, завърши поръчките')
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->poll('60s');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SparePartsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceOrders::route('/'),
            'create' => Pages\CreateServiceOrder::route('/create'),
            'edit' => Pages\EditServiceOrder::route('/{record}/edit'),
        ];
    }
}
