<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScooterResource\Pages;
use App\Filament\Resources\ScooterResource\RelationManagers;
use App\Filament\Resources\ServiceOrderResource;
use App\Models\Scooter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ScooterResource extends Resource
{
    protected static ?string $model = Scooter::class;
    
    protected static ?string $modelLabel = 'Тротинетка';
    protected static ?string $pluralModelLabel = 'Тротинетки';

    protected static ?string $navigationIcon = 'heroicon-o-bolt';
    
    protected static ?string $navigationGroup = 'Клиенти и Тротинетки';
    
    protected static ?int $navigationSort = 2;
    
    public static function getGloballySearchableAttributes(): array
    {
        return ['model', 'serial_number', 'customer.name'];
    }
    
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->model . ' (' . $record->serial_number . ')';
    }
    
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Клиент' => $record->customer->name,
            'Статус' => match($record->status) {
                'in_use' => 'В експлоатация',
                'in_repair' => 'В ремонт',
                'not_working' => 'Неработеща',
                default => $record->status,
            },
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Тротинетка')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Основна информация')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('model')
                                            ->label('Модел')
                                            ->required()
                                            ->maxLength(255)
                                            ->autofocus()
                                            ->placeholder('Въведете модел на тротинетката')
                                            ->helperText('Например: Xiaomi Mi Pro 2, Ninebot Max G30 и т.н.'),
                                            
                                        Forms\Components\TextInput::make('serial_number')
                                            ->label('Сериен номер')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(ignoreRecord: true)
                                            ->placeholder('Въведете сериен номер')
                                            ->helperText('Уникален номер, намиращ се на рамата или под дъното'),
                                            
                                        Forms\Components\Select::make('customer_id')
                                            ->label('Собственик')
                                            ->relationship('customer', 'name')
                                            ->required()
                                            ->searchable()
                                            ->preload()
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
                                            ]),
                                            
                                        Forms\Components\Select::make('status')
                                            ->label('Статус')
                                            ->options([
                                                'in_use' => 'В експлоатация',
                                                'in_repair' => 'В ремонт',
                                                'not_working' => 'Неработеща',
                                            ])
                                            ->required()
                                            ->default('in_use')
                                            ->live()
                                            ->dehydrated()
                                            ->suffixIcon('heroicon-o-flag'),
                                    ]),
                            ]),
                        
                        Forms\Components\Tabs\Tab::make('Технически спецификации')
                            ->icon('heroicon-o-cog-8-tooth')
                            ->schema([
                                Forms\Components\Section::make()
                                    ->schema([
                                        Forms\Components\Card::make()
                                            ->schema([
                                                Forms\Components\Grid::make(3)
                                                    ->schema([
                                                        Forms\Components\TextInput::make('max_speed')
                                                            ->label('Максимална скорост')
                                                            ->numeric()
                                                            ->suffix('км/ч')
                                                            ->placeholder('25')
                                                            ->suffixIcon('heroicon-o-bolt'),
                                                            
                                                        Forms\Components\TextInput::make('battery_capacity')
                                                            ->label('Капацитет на батерията')
                                                            ->numeric()
                                                            ->suffix('mAh')
                                                            ->placeholder('7800')
                                                            ->suffixIcon('heroicon-o-battery-50'),
                                                            
                                                        Forms\Components\TextInput::make('weight')
                                                            ->label('Тегло')
                                                            ->numeric()
                                                            ->suffix('кг')
                                                            ->placeholder('12.5')
                                                            ->suffixIcon('heroicon-o-scale'),
                                                    ]),
                                            ]),
                                            
                                        Forms\Components\RichEditor::make('specifications')
                                            ->label('Спецификации')
                                            ->placeholder('Допълнителни технически параметри, забележки и информация...')
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'bulletList',
                                                'orderedList',
                                            ])
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                            
                        Forms\Components\Tabs\Tab::make('История на ремонтите')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->schema([
                                Forms\Components\Placeholder::make('service_orders_info')
                                    ->label('История на сервизните поръчки')
                                    ->content(function ($record) {
                                        if (!$record || !$record->exists) {
                                            return 'Записът трябва да бъде запазен преди да видите историята на ремонтите.';
                                        }
                                        
                                        $ordersCount = $record->serviceOrders()->count();
                                        
                                        if ($ordersCount === 0) {
                                            return 'Няма регистрирани сервизни поръчки за тази тротинетка.';
                                        }
                                        
                                        $latestOrder = $record->serviceOrders()->latest('created_at')->first();
                                        $completedCount = $record->serviceOrders()->where('status', 'completed')->count();
                                        
                                        return "Общо поръчки: {$ordersCount} | Завършени: {$completedCount} | Последна поръчка: {$latestOrder->created_at->format('d.m.Y')}";
                                    }),
                                    
                                Forms\Components\View::make('filament.scooter.service-history')
                                    ->visible(fn ($record) => $record && $record->exists),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('model')
                    ->label('Модел')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-bolt')
                    ->description(fn ($record) => $record->serial_number),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in_use' => 'В експлоатация',
                        'in_repair' => 'В ремонт',
                        'not_working' => 'Неработеща',
                        default => $state,
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'in_use' => 'heroicon-o-check-circle',
                        'in_repair' => 'heroicon-o-wrench', 
                        'not_working' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in_use' => 'success',
                        'in_repair' => 'warning',
                        'not_working' => 'danger',
                        default => 'gray',
                    }),
                    
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Собственик')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user'),
                    
                Tables\Columns\TextColumn::make('specifications')
                    ->label('Технически параметри')
                    ->formatStateUsing(function ($record) {
                        $specs = [];
                        
                        if ($record->max_speed) {
                            $specs[] = "{$record->max_speed} км/ч";
                        }
                        
                        if ($record->battery_capacity) {
                            $specs[] = "{$record->battery_capacity} mAh";
                        }
                        
                        if ($record->weight) {
                            $specs[] = "{$record->weight} кг";
                        }
                        
                        return implode(' | ', $specs);
                    })
                    ->wrap()
                    ->searchable()
                    ->icon('heroicon-o-cog-8-tooth'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Добавена на')
                    ->date('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Последна промяна')
                    ->date('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'in_use' => 'В експлоатация',
                        'in_repair' => 'В ремонт',
                        'not_working' => 'Неработеща',
                    ]),
                    
                Tables\Filters\SelectFilter::make('customer')
                    ->label('Собственик')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->actionsPosition(ActionsPosition::BeforeColumns)
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Преглед')
                        ->icon('heroicon-o-eye'),
                        
                    Tables\Actions\EditAction::make()
                        ->label('Редактиране')
                        ->icon('heroicon-o-pencil'),
                        
                    Tables\Actions\Action::make('createServiceOrder')
                        ->label('Нова сервизна поръчка')
                        ->icon('heroicon-o-wrench-screwdriver')
                        ->color('warning')
                        ->url(fn (Scooter $record): string => 
                            ServiceOrderResource::getUrl('create', [
                                'customer_id' => $record->customer_id,
                                'customer_name' => $record->customer->name,
                                'scooter_id' => $record->id,
                                'scooter_model' => $record->model,
                            ])
                        ),
                ])
                ->tooltip('Действия')
                ->button()
                ->dropdownPlacement('bottom-start')
                ->color('gray')
                ->label('Действия')
                ->size('xs'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Изтриване на избраните')
                        ->icon('heroicon-o-trash'),
                    
                    Tables\Actions\BulkAction::make('updateStatus')
                        ->label('Промени статуса')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('Нов статус')
                                ->options([
                                    'in_use' => 'В експлоатация',
                                    'in_repair' => 'В ремонт',
                                    'not_working' => 'Неработеща',
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            foreach ($records as $record) {
                                $record->update(['status' => $data['status']]);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ServiceOrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListScooters::route('/'),
            'create' => Pages\CreateScooter::route('/create'),
            'edit' => Pages\EditScooter::route('/{record}/edit'),
        ];
    }
}
