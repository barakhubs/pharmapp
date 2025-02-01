<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Filament\Resources\PurchaseResource\RelationManagers;
use App\Models\Medicine;
use App\Models\Purchase;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationGroup = 'Stock Management';
    protected static ?string $heading = 'Custom Page Heading';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('supplier_id')
                    ->label('Select Supplier')
                    ->required()
                    ->searchable(['name', 'address', 'contact_person'])
                    ->preload()
                    ->native(false)
                    ->relationship('supplier', 'name')
                    ->live()
                    ->afterStateUpdated(function (Set $set) {
                        $set('purchaseItems', []);
                        $set('total_cost', '0.00');
                    })
                    ->columnSpanFull(),

                    Forms\Components\Repeater::make('purchaseItems')
                    ->label('Stock items')
                    ->schema([
                        Forms\Components\Select::make('medicine_id')
                            ->label('Select Medicine')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->options(function (callable $get) {
                                $supplierId = $get('../../supplier_id');
                                if (!$supplierId) {
                                    return [];
                                }
                                return Medicine::where('supplier_id', $supplierId)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, $get) {
                                if ($state) {
                                    $medicine = Medicine::find($state);
                                    $set('price', number_format($medicine->buying_price, 2));
                                    $total = $medicine->buying_price * $get('quantity');
                                    $set('total', number_format($total, 2));
                                }
                            }),

                        Grid::make()
                            ->schema([
                                Forms\Components\TextInput::make('quantity')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, $get) {
                                        $price = floatval(str_replace(',', '', $get('price') ?? 0));
                                        $quantity = floatval($state);
                                        $total = $price * $quantity;
                                        $set('total', number_format($total, 2));
                                    }),

                                Forms\Components\TextInput::make('price')
                                    ->prefix('UGX ')
                                    ->mask(RawJs::make('$money($input)'))
                                    ->label('Unit Price')
                                    ->dehydrated(true)
                                    ->afterStateUpdated(function ($state, Set $set, $get) {
                                        $price = floatval(str_replace(',', '', $get('price') ?? 0));
                                        $quantity = floatval($state);
                                        $total = $price * $quantity;
                                        $set('total', number_format($total, 2));
                                    }),

                            ])->columns(2),

                        Forms\Components\TextInput::make('total')
                            ->prefix('UGX ')
                            ->mask(RawJs::make('$money($input)'))
                            ->label('Total Cost')
                            ->disabled()
                            ->dehydrated(true), // Ensure inclusion in the data
                    ])
                    ->addActionLabel('Add stock item')
                    ->collapsible()
                    ->collapsed(true)
                    ->cloneable()
                    ->columnSpan(2)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, Set $set) {
                        $totalCost = 0;
                        if (!empty($state)) {
                            $totalCost = collect($state)
                                ->sum(function ($item) {
                                    $price = floatval(str_replace(',', '', $item['price'] ?? 0));
                                    $quantity = floatval($item['quantity'] ?? 1);
                                    return $price * $quantity;
                                });
                        }
                        $set('total_cost', number_format($totalCost, 2));
                    }),


                Forms\Components\TextInput::make('total_cost')
                    ->prefix('UGX ')
                    ->mask(RawJs::make('$money($input)'))
                    ->label('Total Purchase Cost')
                    ->disabled()
                    ->columnSpanFull()
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('purchase_number')
                    ->prefix('#')
                    ->label('Purchase No.')
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_cost')
                    ->money('UGX ')
                    ->searchable()
                    ->summarize(summarizers: Sum::make()->money('UGX ')->label('Total')),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\ViewAction::make()
                // ->slideOver()
                // ->modalWidth(MaxWidth::Medium),
                Tables\Actions\Action::make('Print Receipt')->icon('heroicon-m-printer'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePurchases::route('/'),
        ];
    }
}
