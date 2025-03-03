<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceOrderResource\Pages;
use App\Filament\Resources\ServiceOrderResource\RelationManagers;
use App\Models\ServiceOrder;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
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
                                                        'waiting_payment' => 'Чака плащане',
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
                                                    ->suffixIcon('heroicon-o-currency-euro')
                                                    ->live()
                                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                                        $price = (float) ($get('price') ?? 0);
                                                        $amountPaid = (float) ($get('amount_paid') ?? 0);
                                                        
                                                        // Update payment status based on the updated price
                                                        if ($amountPaid <= 0) {
                                                            $set('payment_status', 'unpaid');
                                                        } elseif ($amountPaid >= $price) {
                                                            $set('payment_status', 'paid');
                                                        } else {
                                                            $set('payment_status', 'partially_paid');
                                                        }
                                                    }),
                                                
                                                Forms\Components\Select::make('assigned_to')
                                                    ->label('Възложено на техник')
                                                    ->relationship('technician', 'name')
                                                    ->searchable()
                                                    ->suffixIcon('heroicon-o-user'),
                                            ]),
                                    ]),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make('Плащане')
                            ->icon('heroicon-o-banknotes')
                            ->schema([
                                Forms\Components\Section::make('Информация за плащането')
                                    ->description('Управление на плащанията за тази сервизна поръчка')
                                    ->aside()
                                    ->schema([
                                        Forms\Components\Select::make('payment_status')
                                            ->label('Статус на плащане')
                                            ->options([
                                                'unpaid' => 'Неплатено',
                                                'partially_paid' => 'Частично платено',
                                                'paid' => 'Платено изцяло',
                                            ])
                                            ->required()
                                            ->default('unpaid')
                                            ->live()
                                            ->suffixIcon('heroicon-o-clipboard-document-check')
                                            ->afterStateUpdated(function (Get $get, Set $set, string $state) {
                                                $price = (float) ($get('price') ?? 0);
                                                
                                                if ($state === 'paid') {
                                                    $set('amount_paid', $price);
                                                    if (!$get('payment_date')) {
                                                        $set('payment_date', now());
                                                    }
                                                } elseif ($state === 'unpaid') {
                                                    $set('amount_paid', 0);
                                                }
                                            }),
                                        
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\TextInput::make('amount_paid')
                                                    ->label('Платена сума')
                                                    ->numeric()
                                                    ->prefix('лв')
                                                    ->default(0)
                                                    ->suffixIcon('heroicon-o-currency-euro')
                                                    ->live()
                                                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                                        $price = (float) ($get('price') ?? 0);
                                                        $amountPaid = (float) ($state ?? 0);
                                                        
                                                        if ($amountPaid <= 0) {
                                                            $set('payment_status', 'unpaid');
                                                        } elseif ($amountPaid >= $price) {
                                                            $set('payment_status', 'paid');
                                                        } else {
                                                            $set('payment_status', 'partially_paid');
                                                        }
                                                        
                                                        if ($amountPaid > 0 && !$get('payment_date')) {
                                                            $set('payment_date', now());
                                                        }
                                                    }),
                                                
                                                Forms\Components\Placeholder::make('remaining_amount')
                                                    ->label('Остатък за плащане')
                                                    ->content(function (Get $get) {
                                                        $price = (float) ($get('price') ?? 0);
                                                        $amountPaid = (float) ($get('amount_paid') ?? 0);
                                                        $remainingAmount = max(0, $price - $amountPaid);
                                                        
                                                        return number_format($remainingAmount, 2) . ' лв.';
                                                    }),
                                            ]),
                                        
                                        Forms\Components\Grid::make(2)
                                            ->schema([
                                                Forms\Components\Select::make('payment_method')
                                                    ->label('Начин на плащане')
                                                    ->options([
                                                        'cash' => 'В брой',
                                                        'card' => 'Карта',
                                                        'bank_transfer' => 'Банков превод',
                                                        'other' => 'Друго',
                                                    ])
                                                    ->visible(fn (Get $get) => $get('payment_status') && $get('payment_status') !== 'unpaid'),
                                                
                                                Forms\Components\DatePicker::make('payment_date')
                                                    ->label('Дата на плащане')
                                                    ->visible(fn (Get $get) => $get('payment_status') && $get('payment_status') !== 'unpaid'),
                                            ]),
                                        
                                        Forms\Components\Textarea::make('payment_notes')
                                            ->label('Бележки за плащането')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                        
                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('recordFullPayment')
                                                ->label('Плащане в пълен размер')
                                                ->icon('heroicon-o-banknotes')
                                                ->color('success')
                                                ->action(function (Get $get, Set $set) {
                                                    $set('payment_status', 'paid');
                                                    $set('amount_paid', $get('price'));
                                                    $set('payment_date', now());
                                                })
                                                ->visible(fn (Get $get): bool => 
                                                    $get('payment_status') !== null && 
                                                    $get('payment_status') !== 'paid' && 
                                                    (float) ($get('price') ?? 0) > 0
                                                ),
                                        ]),
                                    ]),
                            ])
                            ->visible(fn (Get $get): bool => $get('status') !== 'cancelled'),
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
                        'waiting_payment' => 'Чака плащане',
                        'completed' => 'Завършена',
                        'cancelled' => 'Отказана',
                        default => $state,
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'pending' => 'heroicon-o-clock',
                        'in_progress' => 'heroicon-o-play',
                        'waiting_payment' => 'heroicon-o-banknotes',
                        'completed' => 'heroicon-o-check-circle', 
                        'cancelled' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'in_progress' => 'warning',
                        'waiting_payment' => 'info',
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
                
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Плащане')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'unpaid' => 'Неплатено',
                        'partially_paid' => 'Частично',
                        'paid' => 'Платено',
                        default => $state,
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'unpaid' => 'heroicon-o-x-circle',
                        'partially_paid' => 'heroicon-o-exclamation-triangle',
                        'paid' => 'heroicon-o-check-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'unpaid' => 'danger',
                        'partially_paid' => 'warning',
                        'paid' => 'success',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('amount_paid')
                    ->label('Платено')
                    ->money('BGN')
                    ->sortable()
                    ->toggleable()
                    ->visible(fn ($record) => $record && $record->payment_status !== 'unpaid')
                    ->icon('heroicon-o-banknotes'),
                
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
                        'waiting_payment' => 'Чака плащане',
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
                    
                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('Статус на плащане')
                    ->options([
                        'unpaid' => 'Неплатено',
                        'partially_paid' => 'Частично платено',
                        'paid' => 'Платено изцяло',
                    ]),
                    
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Начин на плащане')
                    ->options([
                        'cash' => 'В брой',
                        'card' => 'Карта',
                        'bank_transfer' => 'Банков превод',
                        'other' => 'Друго',
                    ]),
                ], layout: FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->filtersTriggerAction(
                fn (Tables\Actions\Action $action) => $action
                    ->button()
                    ->label('Филтри')
            )
            ->persistFiltersInSession()
            ->filtersApplyAction(
                fn (Tables\Actions\Action $action) => $action
                    ->button()
                    ->label('Приложи филтрите')
            )
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
                    
                    Tables\Actions\Action::make('markWaitingPayment')
                        ->label('Готова - чака плащане')
                        ->icon('heroicon-o-banknotes')
                        ->color('info')
                        ->visible(fn (ServiceOrder $record): bool => 
                            $record->status === 'in_progress'
                        )
                        ->action(function (ServiceOrder $record): void {
                            $record->update([
                                'status' => 'waiting_payment',
                            ]);
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Готова за плащане')
                        ->modalDescription('Сигурни ли сте, че искате да маркирате тази поръчка като готова за плащане?')
                        ->modalSubmitActionLabel('Да, чака плащане'),
                        
                    Tables\Actions\Action::make('complete')
                        ->label('Завърши поръчката')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn (ServiceOrder $record): bool => 
                            $record->status === 'waiting_payment' && 
                            $record->payment_status === 'paid'
                        )
                        ->action(function (ServiceOrder $record): void {
                            $record->update([
                                'status' => 'completed',
                                'completed_at' => now(),
                            ]);
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Завърши поръчката')
                        ->modalDescription('Сигурни ли сте, че искате да маркирате тази поръчка като напълно завършена? Клиентът ще получи имейл.')
                        ->modalSubmitActionLabel('Да, завърши поръчката'),
                    
                    Tables\Actions\Action::make('recordPayment')
                        ->label('Запиши плащане')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->visible(fn (ServiceOrder $record): bool => $record && $record->payment_status !== 'paid')
                        ->form([
                            Forms\Components\TextInput::make('amount')
                                ->label('Сума')
                                ->required()
                                ->numeric()
                                ->prefix('лв')
                                ->default(function (ServiceOrder $record) {
                                    return $record->getRemainingAmountAttribute();
                                }),
                            Forms\Components\Select::make('payment_method')
                                ->label('Начин на плащане')
                                ->options([
                                    'cash' => 'В брой',
                                    'card' => 'Карта',
                                    'bank_transfer' => 'Банков превод',
                                    'other' => 'Друго',
                                ])
                                ->required(),
                            Forms\Components\TextInput::make('reference_number')
                                ->label('Референтен номер')
                                ->helperText('Номер на фактура, банков превод и т.н.')
                                ->visible(fn (Get $get): bool => in_array($get('payment_method'), ['bank_transfer', 'card']))
                                ->maxLength(255),
                            Forms\Components\Textarea::make('notes')
                                ->label('Бележки')
                                ->rows(2),
                        ])
                        ->action(function (ServiceOrder $record, array $data): void {
                            $record->addPayment(
                                amount: (float) $data['amount'],
                                method: $data['payment_method'],
                                notes: $data['notes'] ?? null,
                                referenceNumber: $data['reference_number'] ?? null
                            );
                        }),
                    
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
                ->button()
                ->color('gray')
                ->label('Действия')
                ->size('xs'),
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
                        
                    Tables\Actions\BulkAction::make('markAsWaitingPayment')
                        ->label('Маркирай - чакат плащане')
                        ->icon('heroicon-o-banknotes')
                        ->color('info')
                        ->action(function (Collection $records): void {
                            $records->each(function ($record) {
                                if ($record->status === 'in_progress') {
                                    $record->update([
                                        'status' => 'waiting_payment',
                                    ]);
                                }
                            });
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Маркирай избраните като чакащи плащане')
                        ->modalDescription('Сигурни ли сте, че искате да маркирате избраните поръчки като чакащи плащане?')
                        ->modalSubmitActionLabel('Да, чакат плащане')
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('markAsCompleted')
                        ->label('Маркирай като завършени')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each(function ($record) {
                                if ($record->status === 'waiting_payment' && $record->payment_status === 'paid') {
                                    $record->update([
                                        'status' => 'completed',
                                        'completed_at' => now(),
                                    ]);
                                }
                            });
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Маркирай избраните като завършени')
                        ->modalDescription('Сигурни ли сте, че искате да маркирате избраните поръчки като завършени? Клиентите ще получат имейли.')
                        ->modalSubmitActionLabel('Да, завърши поръчките')
                        ->deselectRecordsAfterCompletion(),
                        
                    Tables\Actions\BulkAction::make('markAsPaid')
                        ->label('Маркирай като платени')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $records->each(function ($record) {
                                if ($record->payment_status !== 'paid') {
                                    $record->update([
                                        'payment_status' => 'paid',
                                        'amount_paid' => $record->price,
                                        'payment_date' => now(),
                                    ]);
                                }
                            });
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Маркирай избраните като платени')
                        ->modalDescription('Сигурни ли сте, че искате да маркирате избраните поръчки като платени в пълен размер?')
                        ->modalSubmitActionLabel('Да, маркирай като платени')
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
            RelationManagers\PaymentsRelationManager::class,
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
