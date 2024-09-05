<?php

namespace App\Filament\Widgets;

use App\Models\Doctor;
use App\Models\Appointment;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DasDoctorInfoWidgets extends BaseWidget
{
    protected static string $routePath = 'doctor';
    protected static ?int $sort = 0;
    protected function getStats(): array
    {
        $doctorId = auth()->user()->doctor->id ?? '';

        $today = Carbon::today();


        $todayAppointments = Appointment::where('doctor_id', $doctorId)
            ->whereDate('date', $today)
            ->count();
        $upcomingAppointments = Appointment::where('doctor_id', $doctorId)
            ->whereDate('date', '>', $today)
            ->count();
        $previousAppointments = Appointment::where('doctor_id', $doctorId)
            ->whereDate('date', '<', $today)
            ->count();

        return [
            Stat::make('Appointments Today', $todayAppointments)
                ->description('Appointments scheduled for today')
                ->color('success')
                ->chart([0, $todayAppointments]),

            Stat::make('Upcoming Appointments', $upcomingAppointments)
                ->description('Appointments scheduled in the future')
                ->color('warning')
                ->chart([0, $upcomingAppointments]),

            Stat::make('Previous Appointments', $previousAppointments)
                ->description('Appointments that have already passed')
                ->color('danger')
                ->chart([0, $previousAppointments]),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->role === 'doctor';
    }
}
