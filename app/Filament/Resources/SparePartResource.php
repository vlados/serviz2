<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SparePartResource\Pages;
use App\Filament\Resources\SparePartResource\RelationManagers;
use App\Models\SparePart;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SparePartResource extends Resource
{
    protected static ?string $model = SparePart::class;
    
    protected static ?string $modelLabel = 'Резервна Част';
    protected static ?string $pluralModelLabel = 'Резервни Части';

    protected static ?string $navigationIcon = 'heroicon-o-cog';
        
    protected static ?int $navigationSort = 2;
    
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'part_number', 'description'];
    }
    
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name;
    }
    
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Номер на част' => $record->part_number,
            'Наличност' => $record->stock_quantity . ' бр.',
            'Цена' => number_format($record->selling_price, 2) . ' лв.',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Данни за резервната част')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Име')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('part_number')
                            ->label('Номер на част')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),
                    
                Forms\Components\Section::make('Склад')
                    ->schema([
                        Forms\Components\TextInput::make('stock_quantity')
                            ->label('Количество')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->required(),
                        Forms\Components\TextInput::make('purchase_price')
                            ->label('Покупна цена')
                            ->numeric()
                            ->prefix('лв')
                            ->default(0),
                        Forms\Components\TextInput::make('selling_price')
                            ->label('Продажна цена')
                            ->numeric()
                            ->prefix('лв')
                            ->default(0),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Активна')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Име')
                    ->searchable(),
                Tables\Columns\TextColumn::make('part_number')
                    ->label('Номер на част')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Количество')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('purchase_price')
                    ->label('Покупна цена')
                    ->money('BGN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('selling_price')
                    ->label('Продажна цена')
                    ->money('BGN')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активна')
                    ->boolean()
                    ->sortable(),
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
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Статус')
                    ->options([
                        true => 'Активна',
                        false => 'Неактивна',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Преглед')
                        ->icon('heroicon-o-eye'),
                        
                    Tables\Actions\EditAction::make()
                        ->label('Редактиране')
                        ->icon('heroicon-o-pencil'),
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
                        ->label('Изтриване на избраните'),
                ]),
            ]);
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
            'index' => Pages\ListSpareParts::route('/'),
            'create' => Pages\CreateSparePart::route('/create'),
            'edit' => Pages\EditSparePart::route('/{record}/edit'),
        ];
    }
}