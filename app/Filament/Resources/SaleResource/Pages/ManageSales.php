<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\SaleItem;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManageSales extends ManageRecords
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Order')
                ->slideOver()
                ->modalWidth(MaxWidth::Medium)
                ->using(function (array $data) {
                    return DB::transaction(function () use ($data) {
                        $sale = new Sale();
                        $sale->customer_id = $data['customer_id'];
                        $sale->total_amount = 0.0;
                        $sale->save();

                        $total_cost = 0.0;
                        foreach ($data['orderItems'] as $item) {
                            $price = (float) str_replace(',', '', $item['price']);

                            SaleItem::create([
                                'sale_id' => $sale->id,
                                'medicine_id' => $item['medicine_id'],
                                'quantity' => $item['quantity'],
                                'price' => $price,
                                'total' => $price * $item['quantity'],
                            ]);

                            $medicine = Medicine::find($item['medicine_id']);
                            $medicine->update([
                                'stock_quantity' => $medicine->stock_quantity - $item['quantity']
                            ]);
                            $item_price = str_replace(',', '', $item['price']);
                            $total_cost += ($item['quantity'] * (float)$item_price);
                        }

                        Log::info("Total cost: " .$total_cost);
                        $sale->update([
                            'total_amount' => $total_cost,
                        ]);

                        return $sale;
                    });
                }),
        ];
    }
}
