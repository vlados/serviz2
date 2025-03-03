<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Models\Scooter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ScootersRelationManager extends RelationManager
{
    protected static string $relationship = 'scooters';
    
    protected static ?string $title = 'Тротинетки';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('model')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('serial_number')
                    ->required()
                    ->maxLength(255)
                    ->unique(Scooter::class, 'serial_number', ignoreRecord: true),
                Forms\Components\Select::make('status')
                    ->options([
                        'in_use' => 'In Use',
                        'in_repair' => 'In Repair',
                        'not_working' => 'Not Working',
                    ])
                    ->required(),
                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\TextInput::make('max_speed')
                            ->numeric()
                            ->suffix('km/h'),
                        Forms\Components\TextInput::make('battery_capacity')
                            ->numeric()
                            ->suffix('mAh'),
                        Forms\Components\TextInput::make('weight')
                            ->numeric()
                            ->suffix('kg'),
                    ])->columns(3),
                Forms\Components\Textarea::make('specifications')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('model')
            ->columns([
                Tables\Columns\TextColumn::make('model')
                    ->searchable(),
                Tables\Columns\TextColumn::make('serial_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in_use' => 'success',
                        'in_repair' => 'warning',
                        'not_working' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('serviceOrders_count')
                    ->counts('serviceOrders')
                    ->label('Service History'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actionsPosition(ActionsPosition::BeforeColumns)
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->label('Редактиране')
                        ->icon('heroicon-o-pencil'),
                        
                    Tables\Actions\DeleteAction::make()
                        ->label('Изтриване')
                        ->icon('heroicon-o-trash'),
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
                    Tables\Actions\DeleteBulkAction::make(),
                
                ]),
            ]);
    }
}