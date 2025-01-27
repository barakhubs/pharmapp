<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManagePurchases extends ManageRecords
{
    protected static string $resource = PurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('New Purchase')
                ->slideOver()
                ->modalWidth(MaxWidth::Medium)
                ->using(function (array $data) {
                    return DB::transaction(function () use ($data) {
                        $purchase = new Purchase();
                        $purchase->supplier_id = $data['supplier_id'];
                        $purchase->total_cost = 0.0;
                        $purchase->save();

                        $total_cost = 0.0;
                        $total_quantity = 0;
                        foreach ($data['purchaseItems'] as $item) {
                            $price = (float) str_replace(',', '', $item['price']);

                            PurchaseItem::create([
                                'purchase_id' => $purchase->id,
                                'medicine_id' => $item['medicine_id'],
                                'quantity' => $item['quantity'],
                                'price' => $price,
                                'total' => $price * $item['quantity'],
                            ]);

                            $medicine = Medicine::find($item['medicine_id']);
                            $medicine->update([
                                'stock_quantity' => $medicine->stock_quantity + $item['quantity']
                            ]);
                            $total_cost += ($item['quantity'] * $item['price']);
                            $total_quantity += $item['quantity'];
                        }


                        $purchase->update([
                            'total_cost' => $total_cost,
                        ]);



                        return $purchase;
                    });
                }),
        ];
    }
}
