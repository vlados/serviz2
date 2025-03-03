<?php

namespace App\Filament\Resources\ScooterResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'serviceOrders';
    
    protected static ?string $title = 'Сервизни поръчки';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('order_number')
                    ->required()
                    ->maxLength(255)
                    ->default(fn () => 'SO-' . now()->format('Ymd') . '-' . random_int(1000, 9999))
                    ->disabled(),
                Forms\Components\Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\DatePicker::make('received_at')
                    ->required()
                    ->default(now()),
                Forms\Components\DatePicker::make('completed_at'),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required()
                    ->default('pending'),
                Forms\Components\Textarea::make('problem_description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('work_performed')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('labor_hours')
                    ->numeric()
                    ->default(0)
                    ->minValue(0)
                    ->step(0.5),
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->prefix('$')
                    ->default(0),
                Forms\Components\Select::make('assigned_to')
                    ->label('Assigned Technician')
                    ->options(User::role('technician')->pluck('name', 'id'))
                    ->searchable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('order_number')
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('received_at')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('technician.name')
                    ->label('Technician')
                    ->toggleable(),
            ])
            ->actionsPosition(ActionsPosition::BeforeColumns)
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
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