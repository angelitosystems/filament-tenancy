<?php

namespace AngelitoSystems\FilamentTenancy\Widgets;

use AngelitoSystems\FilamentTenancy\Models\Tenant;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class TenantsChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    public function getHeading(): string | Htmlable | null
    {
        return __('filament-tenancy::tenancy.widgets.tenants_chart_heading');
    }

    protected function getData(): array
    {
        $data = Tenant::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Si no hay datos, retornar estructura vacía
        if ($data->isEmpty()) {
            return [
                'datasets' => [
                    [
                        'label' => __('filament-tenancy::tenancy.widgets.tenants_created'),
                        'data' => array_fill(0, 7, 0),
                        'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                        'borderColor' => 'rgb(59, 130, 246)',
                    ],
                ],
                'labels' => collect(range(6, 0))->map(fn($i) => now()->subDays($i)->format('M d'))->toArray(),
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => __('filament-tenancy::tenancy.widgets.tenants_created'),
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
            ],
            'labels' => $data->pluck('date')->map(fn($date) => \Carbon\Carbon::parse($date)->format('M d'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

