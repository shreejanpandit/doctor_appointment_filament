<?php

namespace App\Filament\Widgets;

use App\Models\Department;
use Filament\Widgets\ChartWidget;

class DasChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = 'Department Doctor Count';

    protected static array $colors = [
        'rgb(255, 99, 132)', // Red
        'rgb(54, 162, 235)', // Blue
        'rgb(255, 205, 86)', // Yellow
        'rgb(75, 192, 192)', // Green
        'rgb(153, 102, 255)', // Purple
        'rgb(255, 159, 64)', // Orange
        'rgb(255, 99, 71)', // Tomato
        'rgb(0, 255, 255)', // Cyan
        'rgb(255, 20, 147)', // Deep Pink
        'rgb(255, 69, 0)', // Red-Orange
        'rgb(32, 178, 170)', // Light Sea Green
        'rgb(135, 206, 250)', // Light Sky Blue
        'rgb(255, 105, 180)', // Hot Pink
        'rgb(186, 85, 211)', // Medium Orchid
        'rgb(0, 128, 128)', // Teal
        'rgb(128, 0, 128)', // Purple
        'rgb(255, 0, 0)', // Red
        'rgb(0, 0, 255)', // Blue
        'rgb(0, 255, 0)', // Lime
        'rgb(255, 255, 0)' // Yellow
    ];

    protected function getData(): array
    {
        $departments = Department::withCount('doctors')
            ->get();

        $labels = $departments->pluck('name')->toArray();
        $data = $departments->pluck('doctors_count')->toArray();

        $colors = array_map(function ($index) {
            return self::$colors[$index % count(self::$colors)];
        }, array_keys($labels));

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Doctor Count by Department',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'hoverOffset' => 6
                ],
            ],
        ];
    }
    public static function canView(): bool
    {
        return  auth()->user()->role === 'admin';
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
