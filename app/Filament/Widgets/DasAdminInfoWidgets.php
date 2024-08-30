<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DasAdminInfoWidgets extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Patient', Patient::count())
                ->description('Total patient')
                ->chart([0, 30, 60, 65, 70, 80, 90])
                ->color('rand'),
            Stat::make('Doctor', Doctor::count())
                ->description('Total Doctor')
                ->chart([0, 30, 60, 65, 70, 75, 80])
                ->color('rand'),
            Stat::make('Appointment', Appointment::count())
                ->description('Total Appointment')
                ->chart([0, 30, 60, 65, 70, 75, 80])
                ->color('rand'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->role === 'admin';
    }
}
