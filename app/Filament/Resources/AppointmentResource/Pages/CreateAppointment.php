<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use App\Models\Schedule;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Assign patient ID if not set
        if (empty($data['patient_id'])) {
            $data['patient_id'] = Auth::user()->patient->id;
        }

        // Remove department ID
        unset($data['department_id']);

        // Validate that a schedule exists for the selected doctor and date
        if (!empty($data['doctor_id']) && !empty($data['date'])) {
            $date = \Carbon\Carbon::parse($data['date']);
            $dayOfWeek = strtolower($date->format('l')); // Get the day of the week in lowercase (e.g., 'monday')

            // Retrieve the schedule for the doctor
            $schedule = Schedule::where('doctor_id', $data['doctor_id'])
                ->where('week_day', $dayOfWeek)
                ->first();

            if ($schedule) {
                // Assign the start time from the schedule
                $data['time'] = $schedule->start_time->format('H:i');
            } else {
                // Throw a validation exception if no schedule is found
                throw ValidationException::withMessages([
                    'date' => ['No available schedule for the selected date.'],
                ]);
            }
        }

        return $data;
    }
}
