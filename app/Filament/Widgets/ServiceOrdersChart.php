<?php

namespace App\Filament\Widgets;

use App\Models\ServiceOrder;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ServiceOrdersChart extends ChartWidget
{
    protected static ?string $heading = 'Сервизни поръчки по месеци';

    protected int|string|array $columnSpan = 'full';
    protected static ?string $maxHeight = '300px';


    protected function getData(): array
    {
        $data = ServiceOrder::select(
            DB::raw('to_char(received_at, \'YYYY-MM\') as month'),
            DB::raw('COUNT(*) as count')
        )
            ->where('received_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $labels = [];
        $values = [];

        // Ensure we have data for the last 6 months, even if there are no orders
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i)->format('Y-m');
            $labels[] = Carbon::now()->subMonths($i)->format('M Y');
            $values[] = $data[$month] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Сервизни поръчки',
                    'data' => $values,
                    'backgroundColor' => [
                        'rgba(54, 162, 235, 0.2)',
                    ],
                    'borderColor' => [
                        'rgb(54, 162, 235)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}