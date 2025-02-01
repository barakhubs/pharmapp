<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\MaxWidth;
use App\Http\Helpers\Helper;
use Carbon\Carbon;
use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Can;

class ManageExpenses extends ManageRecords
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->slideOver()->modalWidth(MaxWidth::Medium),
        ];
    }

    public function getTabs(): array
    {

        return [
            'today' => Tab::make()
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereDate('date', today())
                ),
            'this_week' => Tab::make()
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->whereDate('date', '>=', now()->startOfWeek(Carbon::MONDAY))
                        ->whereDate('date', '<=', now()->endOfWeek(Carbon::SUNDAY));
                }),
            'last_week' => Tab::make()
                ->modifyQueryUsing(function (Builder $query) {
                    return $query->whereDate('date', '>=', now()->subWeek()->startOfWeek(Carbon::MONDAY))
                        ->whereDate('date', '<=', now()->subWeek()->endOfWeek(Carbon::SUNDAY));
                }),
            'this_month' => Tab::make()
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereMonth('date', now()->month)
                        ->whereYear('date', now()->year)
                ),
            'last_month' => Tab::make()
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereMonth('date', now()->subMonth()->month)
                        ->whereYear('date', now()->subMonth()->year)
                ),
            'this_year' => Tab::make()
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereYear('date', now()->year)
                ),
            'last_year' => Tab::make()
                ->modifyQueryUsing(
                    fn(Builder $query) =>
                    $query->whereYear('date', now()->subYear())
                ),
        ];
    }
}
