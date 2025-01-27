<?php
namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CustomerSalesChart extends ChartWidget
{
    protected static ?string $heading = 'Sales Per Customer';
    protected static ?int $sort = 3;

    protected static ?string $pollingInterval = '300';

    // Get data based on selected filter
    protected function getData(): array
    {
        // Get the selected time filter (e.g., 'this-week', 'last-week', 'this-month', 'last-month', 'this-year')
        $filter = request()->input('sales_filter', 'this-week'); // default to 'this-week' if no filter is applied

        // Get the date range based on the selected filter
        $dateRange = $this->getDateRange($filter);

        // Fetch top 10 customers by sales within the specified date range
        $salesData = DB::table('sales')
            ->select('customer_id', DB::raw('SUM(total_amount) as total_sales'))
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->groupBy('customer_id')
            ->orderByDesc('total_sales')
            ->limit(10)
            ->get();

        // Format the data for the chart
        $labels = $salesData->pluck('customer_id')->toArray();
        $data = $salesData->pluck('total_sales')->toArray();

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Sales',
                    'data' => $data,
                ],
            ],
        ];
    }

    // Get the date range based on the filter
    protected function getDateRange(string $filter): array
    {
        $today = Carbon::today();
        switch ($filter) {
            case 'last-week':
                return [
                    'start' => $today->subWeek()->startOfWeek(),
                    'end' => $today->subWeek()->endOfWeek(),
                ];
            case 'this-month':
                return [
                    'start' => $today->startOfMonth(),
                    'end' => $today->endOfMonth(),
                ];
            case 'last-month':
                return [
                    'start' => $today->subMonth()->startOfMonth(),
                    'end' => $today->subMonth()->endOfMonth(),
                ];
            case 'this-year':
                return [
                    'start' => $today->startOfYear(),
                    'end' => $today->endOfYear(),
                ];
            case 'this-week':
            default:
                return [
                    'start' => $today->startOfWeek(),
                    'end' => $today->endOfWeek(),
                ];
        }
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
