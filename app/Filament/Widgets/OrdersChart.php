<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrdersChart extends ChartWidget
{
    protected static ?string $heading = 'Sales Per Month';
    protected static ?int $sort = 2;

    protected static ?string $pollingInterval = '300';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $data = Sale::query()
            ->select(
                DB::raw("strftime('%m', created_at) as month"),
                DB::raw('SUM(total_amount) as total')
            )
            ->whereYear('created_at', Carbon::now()->year)
            ->groupBy(DB::raw("strftime('%m', created_at)"))
            ->orderBy('month')
            ->get()
            ->mapWithKeys(function ($item) {
                // Convert '01' to 1 for array indexing
                return [intval($item->month) => $item->total];
            })
            ->toArray();

        // Fill in missing months with 0
        $salesData = array_fill(1, 12, 0);
        foreach ($data as $month => $total) {
            $salesData[$month] = round($total, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Sales',
                    'data' => array_values($salesData),
                    'fill' => 'start',
                    'tension' => 0.3,
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => "function(value) {
                            return '$' + value.toLocaleString();
                        }",
                    ],
                ],
            ],
        ];
    }
}
