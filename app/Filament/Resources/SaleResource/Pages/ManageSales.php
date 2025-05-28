<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\SaleItem;
use Filament\Actions;
use Filament\Notifications\Notification;
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

class ManageSales extends ManageRecords
{
    protected static string $resource = SaleResource::class;

    protected ?string $heading = 'Sales';
    // protected ?string $subheading = 'Medicine Sales';

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

                        Log::info("Total cost: " . $total_cost);
                        $sale->update([
                            'total_amount' => $total_cost,
                        ]);

                        return $sale;
                    });
                })
                // ->after(fn (Model $record) => route('receipt.print', ['id' => $record->id]))
                // ->openUrlInNewTab(),
        ];
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('New sales record registered')
            ->body('A new sales record has been created successfully.');
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
