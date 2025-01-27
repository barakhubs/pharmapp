<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\SaleResource;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';
    public function table(Table $table): Table
    {
        return $table
            ->query(
                SaleResource::getEloquentQuery()
            )
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_number')
                    ->prefix('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('UGX '),
            ]);
    }
}
