<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class DasDocChartWidget extends ChartWidget
{
    protected static ?int $sort = 1;

    protected static ?string $heading = 'Monthly Appointments';

    protected function getData(): array
    {
        $doctorId = auth()->user()->doctor->id ?? '';
        $currentYear = Carbon::now()->year;

        $months = [];
        $counts = array_fill(0, 12, 0);

        for ($i = 1; $i <= 12; $i++) {
            $months[] = Carbon::create()->month($i)->format('F');
        }

        $appointments = Appointment::selectRaw('strftime("%m", date) as month, COUNT(*) as count')
            ->where('doctor_id', $doctorId)
            ->whereYear('date', $currentYear)
            ->groupByRaw('strftime("%m", date)')
            ->orderByRaw('strftime("%m", date)')
            ->get();

        foreach ($appointments as $appointment) {
            $monthIndex = (int)$appointment->month - 1;
            $counts[$monthIndex] = $appointment->count;
        }

        return [
            'labels' => $months,
            'datasets' => [
                [
                    'label' => 'Appointments by Month',
                    'data' => $counts,
                ],
            ],
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->role === 'doctor';
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
