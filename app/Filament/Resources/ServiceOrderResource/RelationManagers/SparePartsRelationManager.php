<?php

namespace App\Filament\Resources\ServiceOrderResource\RelationManagers;

use App\Models\SparePart;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SparePartsRelationManager extends RelationManager
{
    protected static string $relationship = 'spareParts';
    
    protected static ?string $title = 'Резервни части';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('spare_part_id')
                    ->label('Spare Part')
                    ->options(SparePart::query()->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) => 
                        $set('price_per_unit', SparePart::find($state)?->selling_price ?? 0)),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->minValue(1),
                Forms\Components\TextInput::make('price_per_unit')
                    ->required()
                    ->label('Price Per Unit')
                    ->prefix('$')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('part_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pivot.quantity')
                    ->label('Quantity')
                    ->numeric(),
                Tables\Columns\TextColumn::make('pivot.price_per_unit')
                    ->label('Price Per Unit')
                    ->money('USD'),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('USD')
                    ->getStateUsing(fn ($record) => 
                        $record->pivot->quantity * $record->pivot->price_per_unit),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->label('Редактиране')
                        ->icon('heroicon-o-pencil')
                        ->using(function ($record, array $data): SparePart {
                            $record->pivot->update($data);
                            return $record;
                        }),
                    Tables\Actions\DetachAction::make()
                        ->label('Премахване')
                        ->icon('heroicon-o-x-mark'),
                ])
                ->tooltip('Действия')
                ->button()
                ->color('gray')
                ->label('Действия')
                ->size('xs'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}