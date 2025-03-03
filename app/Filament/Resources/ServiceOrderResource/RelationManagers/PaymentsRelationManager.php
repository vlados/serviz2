<?php

namespace App\Filament\Resources\ServiceOrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function form(Form $form): Form
    {
        return $form
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
                    
                Forms\Components\TextInput::make('reference_number')
                    ->label('Референтен номер')
                    ->helperText('Номер на фактура, банков превод и т.н.')
                    ->maxLength(255),
                
                Forms\Components\Select::make('recorded_by')
                    ->label('Регистрирано от')
                    ->relationship('recordedBy', 'name')
                    ->default(auth()->id())
                    ->required(),
                
                Forms\Components\Textarea::make('notes')
                    ->label('Бележки')
                    ->rows(3),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('amount')
                    ->label('Сума')
                    ->money('BGN')
                    ->sortable(),
                
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
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Референтен номер')
                    ->searchable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('recordedBy.name')
                    ->label('Регистрирано от')
                    ->sortable(),
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
                    Tables\Actions\ViewAction::make()
                        ->label('Преглед')
                        ->icon('heroicon-o-eye'),
                        
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
