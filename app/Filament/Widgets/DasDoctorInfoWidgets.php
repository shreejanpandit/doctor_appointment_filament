<?php

namespace App\Filament\Widgets;

use App\Models\Doctor;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DasDoctorInfoWidgets extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Doctor', Doctor::count())
                ->description('Total doctor')
                ->chart([0, 30, 60, 65, 70, 80, 90])
                ->color('rand'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->role === 'doctor';
    }
}
