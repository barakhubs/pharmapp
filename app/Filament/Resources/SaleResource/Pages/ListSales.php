<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Filament\Resources\SaleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Carbon\Carbon;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSales extends ListRecords
{
    protected static string $resource = SaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
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
