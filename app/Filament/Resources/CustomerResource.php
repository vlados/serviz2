<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Filament\Resources\ScooterResource;
use App\Filament\Resources\ServiceOrderResource;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;
    
    protected static ?string $modelLabel = 'Клиент';
    protected static ?string $pluralModelLabel = 'Клиенти';

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?string $navigationGroup = 'Клиенти и Тротинетки';
    
    protected static ?int $navigationSort = 1;
    
    protected static ?string $recordTitleAttribute = 'name';
    
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'phone', 'email', 'address'];
    }
    
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name;
    }
    
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Телефон' => $record->phone,
            'Имейл' => $record->email,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Име')
                            ->required()
                            ->maxLength(255)
                            ->autofocus()
                            ->placeholder('Въведете име на клиента')
                            ->columnSpan(2),
                            
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('phone')
                                    ->label('Телефон')
                                    ->tel()
                                    ->maxLength(255)
                                    ->placeholder('089xxxxxxx')
                                    ->suffixIcon('heroicon-o-phone'),
                                    
                                Forms\Components\TextInput::make('email')
                                    ->label('Имейл')
                                    ->email()
                                    ->maxLength(255)
                                    ->placeholder('email@example.com')
                                    ->suffixIcon('heroicon-o-envelope'),
                            ]),
                            
                        Forms\Components\Textarea::make('address')
                            ->label('Адрес')
                            ->placeholder('Въведете адрес на клиента')
                            ->maxLength(65535)
                            ->rows(3)
                            ->columnSpanFull(),
                            
                        Forms\Components\RichEditor::make('notes')
                            ->label('Бележки')
                            ->placeholder('Допълнителна информация за клиента...')
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'bulletList',
                                'orderedList',
                            ]),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Име')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->icon('heroicon-o-user')
                    ->copyable(),
                    
                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-phone')
                    ->url(fn ($record) => $record->phone ? "tel:{$record->phone}" : null)
                    ->openUrlInNewTab(),
                    
                Tables\Columns\TextColumn::make('email')
                    ->label('Имейл')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-envelope')
                    ->url(fn ($record) => $record->email ? "mailto:{$record->email}" : null)
                    ->openUrlInNewTab(),
                    
                Tables\Columns\TextColumn::make('scooters_count')
                    ->counts('scooters')
                    ->label('Тротинетки')
                    ->icon('heroicon-o-truck')
                    ->sortable()
                    ->color('primary')
                    ->badge(),
                    
                Tables\Columns\TextColumn::make('service_orders_count')
                    ->counts('serviceOrders')
                    ->label('Сервизни Поръчки')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->sortable()
                    ->color('success')
                    ->badge(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->actionsPosition(ActionsPosition::BeforeColumns)
            ->filters([
                Tables\Filters\Filter::make('has_scooters')
                    ->label('С тротинетки')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->has('scooters')),
                    
                Tables\Filters\Filter::make('has_service_orders')
                    ->label('Със сервизни поръчки')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->has('serviceOrders')),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Преглед')
                        ->icon('heroicon-o-eye'),
                        
                    Tables\Actions\EditAction::make()
                        ->label('Редактиране')
                        ->icon('heroicon-o-pencil'),
                        
                    Tables\Actions\Action::make('addScooter')
                        ->label('Добави тротинетка')
                        ->icon('heroicon-o-plus')
                        ->color('success')
                        ->url(fn (Customer $record): string => 
                            ScooterResource::getUrl('create', [
                                'customer_id' => $record->id,
                                'customer_name' => $record->name,
                            ])
                        ),
                        
                    Tables\Actions\Action::make('addServiceOrder')
                        ->label('Нова сервизна поръчка')
                        ->icon('heroicon-o-plus')
                        ->color('warning')
                        ->url(fn (Customer $record): string => 
                            ServiceOrderResource::getUrl('create', [
                                'customer_id' => $record->id,
                                'customer_name' => $record->name,
                            ])
                        ),
                ])
                ->tooltip('Действия')
                ->dropdownPlacement('bottom-start')
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
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ScootersRelationManager::class,
            RelationManagers\ServiceOrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
