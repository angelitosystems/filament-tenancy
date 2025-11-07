<?php

namespace AngelitoSystems\FilamentTenancy\Widgets;

use AngelitoSystems\FilamentTenancy\Models\Subscription;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class SubscriptionsChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    public function getHeading(): string | Htmlable | null
    {
        return __('filament-tenancy::tenancy.widgets.subscriptions_chart_heading');
    }

    protected function getData(): array
    {
        $statuses = Subscription::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        // Si no hay datos, retornar estructura vacía
        if ($statuses->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'label' => __('filament-tenancy::tenancy.widgets.subscriptions_by_status'),
                        'data' => [],
                        'backgroundColor' => [],
                    ],
                ],
                'labels' => [],
            ];
        }

        $labels = $statuses->pluck('status')->map(fn($status) => ucfirst($status))->toArray();
        $data = $statuses->pluck('count')->toArray();

        // Generar colores dinámicamente según la cantidad de estados
        $colors = [
            'rgba(34, 197, 94, 0.5)',   // success - active
            'rgba(239, 68, 68, 0.5)',    // danger - cancelled
            'rgba(251, 146, 60, 0.5)',   // warning - expired
            'rgba(107, 114, 128, 0.5)',  // gray - inactive
            'rgba(59, 130, 246, 0.5)',   // info - pending
            'rgba(168, 85, 247, 0.5)',   // purple - suspended
        ];

        return [
            'datasets' => [
                [
                    'label' => __('filament-tenancy::tenancy.widgets.subscriptions_by_status'),
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}

