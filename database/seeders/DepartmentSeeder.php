<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('departments')->insert([
            ['name' => 'Cardiology'],
            ['name' => 'Orthopedics'],
            ['name' => 'Neurology'],
            ['name' => 'Pediatrics'],
            ['name' => 'Oncology'],
            ['name' => 'Internal Medicine'],
            ['name' => 'Emergency Medicine'],
            ['name' => 'Radiology'],
            ['name' => 'Anesthesiology'],
            ['name' => 'Dermatology'],
            ['name' => 'Gastroenterology'],
            ['name' => 'Obstetrics and Gynecology'],
            ['name' => 'Urology'],
            ['name' => 'Ophthalmology'],
            ['name' => 'Pulmonology'],
            ['name' => 'Rheumatology'],
            ['name' => 'Nephrology'],
            ['name' => 'Hematology'],
            ['name' => 'Infectious Diseases'],
            ['name' => 'Endocrinology'],
        ]);
    }
}
