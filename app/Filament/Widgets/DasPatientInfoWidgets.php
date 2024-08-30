<?php

namespace App\Filament\Widgets;

use App\Models\Patient;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DasPatientInfoWidgets extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Patient', Patient::count())
                ->description('Total patient')
                ->chart([0, 30, 60, 65, 70, 80, 90])
                ->color('a'),
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()->role === 'patient';
    }
}
