<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResource\Pages;
use App\Filament\Resources\AppointmentResource\RelationManagers;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontFamily;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('patient_id')
                    ->label('Patient')
                    ->hidden(fn() => auth()->user()->role === 'patient')
                    ->options(function () {
                        $patients = Patient::with('user')->get();
                        return $patients->pluck('user.name', 'id')->filter(function ($name) {
                            return !is_null($name);
                        })->toArray();
                    })
                    ->required(),
                Forms\Components\Select::make('department_id')
                    ->label('Department')
                    ->hiddenOn('edit')
                    ->options(function () {
                        $departments = Department::with('doctors')->get();
                        return $departments->pluck('name', 'id')->filter(function ($name) {
                            return !is_null($name);
                        })->toArray();
                    })
                    ->live()
                    ->required(),
                Forms\Components\Select::make('doctor_id')
                    ->label('Doctor')
                    ->hiddenOn('edit')
                    ->options(function (Forms\Get $get) {
                        $doctors = Doctor::with('user')->get();
                        return $doctors->where('department_id', $get('department_id'))->pluck('user.name', 'id')->toArray();
                    })
                    ->disabled(fn(Forms\Get $get): bool => !filled($get('department_id')))
                    ->required(),
                Forms\Components\DatePicker::make('date')
                    ->hiddenOn('edit')
                    ->required(),
                TimePicker::make('time')
                    ->hiddenOn('edit')
                    ->required(),
                Forms\Components\Textarea::make('description'),
            ]);
    }

    // public static function getEloquentQuery(): Builder
    // {
    //     return parent::getEloquentQuery()->where('user_id', auth()->id());
    // }

    public static function table(Table $table): Table
    {
        $userId = auth()->user()->id;
        return $table
            ->modifyQueryUsing(function (Builder $query) use ($userId) {
                if (auth()->user()->role === 'admin') {
                    return;
                } elseif (auth()->user()->role === 'patient') {
                    $query->whereHas('patient', function (Builder $query) use ($userId) {
                        $query->where('user_id', $userId);
                    });
                } elseif (auth()->user()->role === 'doctor') {

                    $query->whereHas('doctor', function (Builder $query) use ($userId) {
                        $query->where('user_id', $userId);
                    });
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('patient.user.name')
                    ->label('Patient')
                    ->numeric()
                    ->hidden(fn() => auth()->user()->role === 'patient')
                    ->sortable(),
                Tables\Columns\TextColumn::make('doctor.user.name')
                    ->label('Doctor')
                    ->hidden(fn() => auth()->user()->role === 'doctor')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('time'),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('doctor_id')
                    ->label('Doctor')
                    ->options(function () {
                        return Doctor::query()
                            ->with('user')
                            ->get()
                            ->pluck('user.name', 'id')
                            ->filter(function ($name) {
                                return !is_null($name);
                            })
                            ->toArray();
                    }),


                Filter::make('date')
                    ->form([
                        DatePicker::make('date')
                            ->label('Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            !empty($data['date']),
                            fn(Builder $query) => $query->whereDate('date', '=', Carbon::parse($data['date'])->format('Y-m-d'))
                        );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (!empty($data['date'])) {
                            $selectedDate = Carbon::parse($data['date'])->toFormattedDateString();
                            $indicators[] = Indicator::make('Appointment on ' . $selectedDate)
                                ->removeField('date');
                        }

                        return $indicators;
                    })

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('Reshedule')
                    ->visible(fn($record) => auth()->user()->role === 'doctor')
                    ->form(function ($record) {
                        return [
                            DatePicker::make('date')
                                ->default($record->date)
                                ->native(false)
                        ];
                    })
                    ->action(function ($record, $data) {
                        $record->date = $data['date'];
                        $record->save();
                    })
                    ->icon('heroicon-m-clock')
                    ->color('success')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Appointment Details')
                    ->columns([
                        'sm' => 1,
                        'xl' => 2,
                        '2xl' => 3,
                    ])
                    ->schema([
                        TextEntry::make('patient.user.name')
                            ->label('Patient')
                            ->icon('heroicon-m-user')
                            ->fontFamily(FontFamily::Mono)
                            ->iconColor('primary'),
                        TextEntry::make('doctor.user.name')
                            ->label('Doctor')
                            ->icon('heroicon-m-user')
                            ->fontFamily(FontFamily::Mono)
                            ->iconColor('primary'),
                        TextEntry::make('date')
                            ->label('Date')
                            ->icon('heroicon-m-calendar')
                            ->iconColor('primary'),
                        TextEntry::make('time')
                            ->label('Time')
                            ->icon('heroicon-m-clock')
                            ->iconColor('primary'),
                        TextEntry::make('description')
                            ->label('Description')
                            ->icon('heroicon-m-document-text')
                            ->iconColor('primary'),
                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->icon('heroicon-m-calendar')
                            ->iconColor('primary'),
                        TextEntry::make('updated_at')
                            ->label('Updated At')
                            ->icon('heroicon-m-calendar')
                            ->iconColor('primary'),
                    ])
            ]);
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'view' => Pages\ViewAppointment::route('/{record}'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
