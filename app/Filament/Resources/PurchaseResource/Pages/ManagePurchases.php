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
use App\Http\Helpers\Helper;
use Carbon\Carbon;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Can;

class ManagePurchases extends ManageRecords
{
    protected static string $resource = PurchaseResource::class;
    protected ?string $heading = 'Purchases';
    protected ?string $subheading = 'Medicine Stocking';

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

                            $total_cost += ((float)$item['quantity'] * (float)$price);
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

    public function getTabs(): array
    {

        return [
            'today' => Tab::make()
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereDate('created_at', today())
                ),
            'this_week' => Tab::make()
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->whereDate('created_at', '>=', now()->startOfWeek(Carbon::MONDAY))
                        ->whereDate('created_at', '<=', now()->endOfWeek(Carbon::SUNDAY));
                }),
            'last_week' => Tab::make()
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->whereDate('created_at', '>=', now()->subWeek()->startOfWeek(Carbon::MONDAY))
                        ->whereDate('created_at', '<=', now()->subWeek()->endOfWeek(Carbon::SUNDAY));
                }),
            'this_month' => Tab::make()
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                ),
            'last_month' => Tab::make()
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereMonth('created_at', now()->subMonth()->month)
                        ->whereYear('created_at', now()->subMonth()->year)
                ),
            'this_year' => Tab::make()
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereYear('created_at', now()->year)
                ),
            'last_year' => Tab::make()
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereYear('created_at', now()->subYear())
                ),
        ];
    }
}
