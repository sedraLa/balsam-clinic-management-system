<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        $cardiology    = Specialty::where('name', 'Cardiology')->first();
        $dermatology   = Specialty::where('name', 'Dermatology')->first();
        $orthopedics   = Specialty::where('name', 'Orthopedics')->first();

        $karimi = User::where('email', 'doctor.karimi@balsam.test')->first();
        $nassar = User::where('email', 'doctor.nassar@balsam.test')->first();
        $fayyad = User::where('email', 'doctor.fayyad@balsam.test')->first();

        Doctor::create([
            'user_id'      => $karimi->id,
            'specialty_id' => $cardiology->id,
            'bio'          => 'Cardiologist with 10 years of experience.',
        ]);

        Doctor::create([
            'user_id'      => $nassar->id,
            'specialty_id' => $dermatology->id,
            'bio'          => 'Specialist in skin conditions and cosmetic dermatology.',
        ]);

        Doctor::create([
            'user_id'      => $fayyad->id,
            'specialty_id' => $orthopedics->id,
            'bio'          => 'Orthopedic surgeon focused on sports injuries.',
        ]);
    }
}
