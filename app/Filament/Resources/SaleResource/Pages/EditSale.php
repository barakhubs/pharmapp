<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\SaleItem;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;

    protected ?string $heading = 'Edit Sale';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load the sale items
        $this->record->load('saleItems.medicine');

        // Prepare order items for the repeater
        $orderItems = [];
        foreach ($this->record->saleItems as $item) {
            $medicine = $item->medicine;
            $orderItems[] = [
                'medicine_id' => $item->medicine_id,
                'medicine_name' => $medicine ? $medicine->name : 'Unknown Medicine',
                'quantity' => $item->quantity,
                'price' => number_format($item->price, 2),
                'total' => number_format($item->total, 2),
                'medicine' => $medicine,
            ];
        }

        // Set the data
        $data['customer_id'] = $this->record->customer_id;
        $data['orderItems'] = $orderItems;
        $data['total_cost'] = number_format($this->record->total_amount, 2);

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            // Store original quantities to restore stock if needed
            $originalItems = $record->saleItems->keyBy('medicine_id');

            // Delete existing sale items
            \App\Models\SaleItem::where('sale_id', $record->id)->delete();

            // Restore stock quantities from original items
            foreach ($originalItems as $originalItem) {
                $medicine = \App\Models\Medicine::find($originalItem->medicine_id);
                if ($medicine) {
                    Log::info("Medicine found: " . $medicine->name);
                    $medicine->update([
                        'stock_quantity' => $medicine->stock_quantity + $originalItem->quantity
                    ]);
                }
            }

            // Update customer
            $record->customer_id = $data['customer_id'];

            $total_cost = 0.0;

            // Create new sale items with updated data
            if (isset($data['orderItems']) && is_array($data['orderItems'])) {
                foreach ($data['orderItems'] as $item) {
                    $price = (float) str_replace(',', '', $item['price'] ?? '0');

                    \App\Models\SaleItem::create([
                        'sale_id' => $record->id,
                        'medicine_id' => $item['medicine_id'],
                        'quantity' => $item['quantity'],
                        'price' => $price,
                        'total' => $price * $item['quantity'],
                    ]);

                    // Update medicine stock with new quantities
                    if (isset($item['medicine_id']) && $item['medicine_id']) {
                        $medicine = \App\Models\Medicine::find($item['medicine_id']);

                        if ($medicine) {
                            Log::info("Medicine found: " . $medicine);
                            $newStockQuantity = $medicine->stock_quantity - $item['quantity'];
                            $medicine->update([
                                'stock_quantity' => max(0, $newStockQuantity) // Ensure stock doesn't go negative
                            ]);
                        }
                    }
                    $total_cost += ($item['quantity'] * $price);
                }
            }

            $record->update([
                'total_amount' => $total_cost,
            ]);

            return $record;
        });
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Sale updated')
            ->body('The sales record has been updated successfully.');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
