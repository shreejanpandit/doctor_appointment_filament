<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\Patient;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DasPatientInfoWidgets extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        // return [
        //     Stat::make('Patient', Patient::count())
        //         ->description('Total patient')
        //         ->chart([0, 30, 60, 65, 70, 80, 90])
        //         ->color('a'),
        // ];

        $patientId = auth()->user()->patient->id ?? '';

        $today = Carbon::today();


        $todayAppointments = Appointment::where('patient_id', $patientId)
            ->whereDate('date', $today)
            ->count();
        $upcomingAppointments = Appointment::where('patient_id', $patientId)
            ->whereDate('date', '>=', $today)
            ->count();

        return [
            Stat::make('Appointments Today', $todayAppointments)
                ->color('success')
                ->chart([0, $todayAppointments]),

            Stat::make('Upcoming Appointments', $upcomingAppointments)
                ->color('warning')
                ->chart([0, $upcomingAppointments]),

        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->role === 'patient';
    }
}
