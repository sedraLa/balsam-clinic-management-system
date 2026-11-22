<?php

namespace Database\Seeders;

use App\Models\Specialty;
use Illuminate\Database\Seeder;

class SpecialtySeeder extends Seeder
{
    public function run(): void
    {
        $specialties = [
            'General Medicine',
            'Cardiology',
            'Dermatology',
            'Orthopedics',
            'Pediatrics',
            'Neurology',
            'Ophthalmology',
            'Gynecology',
        ];

        foreach ($specialties as $name) {
            Specialty::create(['name' => $name]);
        }
    }
}
