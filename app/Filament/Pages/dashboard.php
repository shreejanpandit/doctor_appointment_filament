<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BasePage;
use Illuminate\Support\HtmlString;

class Dashboard extends BasePage
{
    protected static ?string $slug = 'dashboard';
    protected static string $routePath = '/';

    public function getTitle(): string
    {
        $userRole = auth()->user()->role;
        return $userRole === 'doctor' ? 'Doctor Dashboard' : ($userRole === 'patient' ? 'Patient Dashboard' : 'Dashboard');
    }

    public function getSubheading(): HtmlString
    {
        $user = auth()->user();
        $warningMessage = '<div style="
            color: #fff; 
            background-color: #f44336; 
            border: 2px solid #c62828; 
            padding: 15px; 
            border-radius: 5px; 
            margin-top: 20px; 
            font-weight: bold; 
            text-align: center; 
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); 
            ">
            <strong>!!!  Warning:</strong> Please set up your profile to use this system properly  !!!
        </div>';

        if ($user->role === 'doctor' && empty($user->doctor->id)) {
            return new HtmlString($warningMessage);
        }

        if ($user->role === 'patient' && empty($user->patient->id)) {
            return new HtmlString($warningMessage);
        }

        $welcomeMessage = '<div class="text-gray-800 text-lg font-semibold mt-5">
        Welcome, ' . htmlspecialchars($user->name) . '!
    </div>';

        return new HtmlString($welcomeMessage);
    }
}
