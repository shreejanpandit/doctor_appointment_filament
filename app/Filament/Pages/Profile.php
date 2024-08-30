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
    protected static ?string $model = User::class;

    public ?array $data = [];
    protected static string $view = 'filament.pages.profile';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function mount(): void
    {
        $user = User::query()->with(['doctor', 'patient'])->find(auth()->id())->toArray();
        $this->form->fill();
        $this->form->fill($user);
    }

    public function form(Form $form): Form
    {
        $user = auth()->user();

        $schema = [
            TextInput::make('name')
                ->autofocus()
                ->required(),
            TextInput::make('email')
                ->required(),
        ];

        if ($user->role === 'doctor') {
            $schema = array_merge($schema, [
                Select::make('doctor.department_id')
                    ->label('Department')
                    ->relationship('doctor.department', 'name')
                    ->required(),
                TextInput::make('doctor.contact')
                    ->required()
                    ->default($user->doctor->contact ?? ''),
                TextInput::make('doctor.bio')
                    ->required()
                    ->default($user->doctor->bio ?? ''),
                FileUpload::make('doctor.image')
                    ->image()
                    ->default($user->doctor->image ?? '')
                    ->disk('public')
                    ->directory('profile_images'),
            ]);
        } elseif ($user->role === 'patient') {
            $schema = array_merge($schema, [
                DatePicker::make('patient.dob')
                    ->required()
                    ->default($user->patient->dob ?? ''),
                TextInput::make('patient.age')
                    ->required()
                    ->numeric()
                    ->default($user->patient->age ?? ''),
                Select::make('patient.gender')
                    ->options([
                        'male' => 'Male',
                        'female' => 'Female',
                        'other' => 'Other',
                    ])
                    ->required()
                    ->default($user->patient->gender ?? ''),
                FileUpload::make('patient.image')
                    ->image()
                    ->default($user->patient->image ?? '')
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
        $user = User::query()->with(['doctor', 'patient'])->find(auth()->id());

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
            $user->doctor()->updateOrCreate(
                [
                    'user_id' => $user->id
                ],
                [
                    'department_id' => $formState['doctor']['department_id'],
                    'contact' => $formState['doctor']['contact'],
                    'bio' => $formState['doctor']['bio'],
                    'image' => $formState['doctor']['image'],
                ]
            );
        } elseif ($user->role === 'patient') {
            $user->patient()->updateOrCreate(
                [
                    'user_id' => $user->id
                ],
                [
                    'dob' => $formState['patient']['dob'],
                    'age' => $formState['patient']['age'],
                    'gender' => $formState['patient']['gender'],
                    'image' => $formState['patient']['image'],
                ]
            );
        }

        Notification::make()
            ->title('Profile updated!')
            ->success()
            ->send();
    }
}
