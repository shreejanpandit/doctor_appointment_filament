<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Profile extends Page implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];
    protected static string $view = 'filament.pages.profile';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        $user = auth()->user();

        if (!$user) {
            // Handle the case where no user is authenticated
            return $form->schema([]);
        }

        $schema = [
            TextInput::make('name')
                ->autofocus()
                ->required()
                ->default($user->name),
            TextInput::make('email')
                ->required()
                ->default($user->email),
        ];

        if ($user->role === 'doctor') {
            $schema = array_merge($schema, [
                Select::make('department_id')
                    ->label('Department')
                    ->relationship('doctor.department', 'name')
                    ->required()
                    ->default($user->doctor ? $user->doctor->department_id : null),
                TextInput::make('contact')
                    ->required()
                    ->default($user->doctor ? $user->doctor->contact : ''),
                TextInput::make('bio')
                    ->required()
                    ->default($user->doctor ? $user->doctor->bio : ''),
                FileUpload::make('image')
                    ->image()
                    ->default($user->doctor ? $user->doctor->image : '')
                    ->disk('public') // Ensure this matches your filesystem disk
                    ->directory('profile_images'), // Ensure this is correct
            ]);
        } elseif ($user->role === 'patient') {
            $schema = array_merge($schema, [
                DatePicker::make('dob')
                    ->required()
                    ->default($user->patient ? $user->patient->dob : ''),
                TextInput::make('age')
                    ->required()
                    ->numeric()
                    ->default($user->patient ? $user->patient->age : ''),
                Select::make('gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                        'other' => 'Other',
                    ])
                    ->required()
                    ->default($user->patient ? $user->patient->gender : ''),
                FileUpload::make('image')
                    ->image()
                    ->default($user->patient ? $user->patient->image : '')
                    ->disk('public')
                    ->directory('profile_images'),
            ]);
        }

        return $form
            ->schema($schema)
            ->statePath('data')
            ->model($user);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('Update')
                ->color('primary')
                ->submit('Update'),
        ];
    }

    public function update(): void
    {
        $user = auth()->user();

        if (!$user) {
            Notification::make()
                ->title('Update failed!')
                ->danger()
                ->send();
            return;
        }

        $formState = $this->form->getState();

        $user->update([
            'name' => $formState['name'],
            'email' => $formState['email'],
        ]);

        if ($user->role === 'doctor') {
            $doctor = $user->doctor;

            if ($doctor) {
                $doctor->update([
                    'department_id' => $formState['department_id'] ?? $doctor->department_id,
                    'contact' => $formState['contact'] ?? $doctor->contact,
                    'bio' => $formState['bio'] ?? $doctor->bio,
                    'image' => $formState['image'] ?? $doctor->image,
                ]);
            }
        } elseif ($user->role === 'patient') {
            $patient = $user->patient;

            if ($patient) {
                $patient->update([
                    'dob' => $formState['dob'] ?? $patient->dob,
                    'age' => $formState['age'] ?? $patient->age,
                    'gender' => $formState['gender'] ?? $patient->gender,
                    'image' => $formState['image'] ?? $patient->image,
                ]);
            }
        }

        Notification::make()
            ->title('Profile updated!')
            ->success()
            ->send();
    }
}
