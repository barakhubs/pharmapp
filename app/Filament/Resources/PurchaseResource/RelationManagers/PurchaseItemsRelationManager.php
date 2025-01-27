<?php 

namespace App\Filament\Resources\PurchaseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PurchaseItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'purchaseItems';

    protected static ?string $title = 'Purchase Items';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('medicine.name')
                    ->label('Medicine')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('UGX')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->money('UGX')
                    ->sortable(),
            ]);
    }
}
