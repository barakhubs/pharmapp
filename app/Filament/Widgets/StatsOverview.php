<?php

namespace App\Filament\Widgets;

use App\Models\Medicine;
use App\Models\Sale;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';

    protected ?string $heading = 'Analytics';

    protected static ?int $sort = 0;

    protected ?string $description = 'An overview of some analytics.';
    protected function getStats(): array
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $startOfPreviousWeek = Carbon::now()->subWeek()->startOfWeek();
        $endOfPreviousWeek = Carbon::now()->subWeek()->endOfWeek();

        $currentWeekSales = Sale::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                                ->sum('total_amount');

        $previousWeekSales = Sale::whereBetween('created_at', [$startOfPreviousWeek, $endOfPreviousWeek])
                                ->sum('total_amount');

        $salesDifference = $currentWeekSales - $previousWeekSales;

        $formattedCurrentWeekSales = number_format($currentWeekSales, 0, '.', ',');
        $formattedDifference = number_format($salesDifference, 0, '.', ',');

        $weeklyDescription = $salesDifference > 0 ? 'UGX ' . $formattedDifference .' increase' : 'UGX ' . $formattedDifference .' decrease';
        $descriptionIcon = $salesDifference > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';

        $salesPerDay = $currentWeekSales / 7;
        $formattedSalesPerDay = number_format($salesPerDay, 0, '.', ',');

        $salesPerDayPreviousWeek = $previousWeekSales / 7;
        $formattedSalesPerDayPreviousWeek = number_format($salesPerDayPreviousWeek, 0, '.', ',');

        $salesPerDayDifference = $salesPerDay - $salesPerDayPreviousWeek;
        $formattedSalesPerDayDifference = number_format($salesPerDayDifference, 0, '.', ',');

        $salesPerDayDescription = $salesPerDayDifference > 0 ? 'UGX ' . $formattedSalesPerDayDifference .' increase' : 'UGX ' . $formattedSalesPerDayDifference .' decrease';
        $salesPerDayDescriptionIcon = $salesPerDayDifference > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';

        // Stock
        $inStock = Medicine::where('stock_quantity', '>', 0)->count();
        $outOfStock = Medicine::where('stock_quantity', '<=', 0)->count();

        return [
            Stat::make('Weekly sales', 'UGX '.$formattedCurrentWeekSales)
                ->description($weeklyDescription)
                ->descriptionIcon($descriptionIcon),
            Stat::make('Average sales per day', 'UGX '.$formattedSalesPerDay)
                ->description($salesPerDayDescription)
                ->descriptionIcon($salesPerDayDescriptionIcon),
            Stat::make('Stock', $inStock . ' in stock')
                ->description($outOfStock . ' out of stock'),
        ];
    }
}

