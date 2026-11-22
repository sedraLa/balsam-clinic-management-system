<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        // Patients with user accounts
        $khalid = User::where('email', 'patient.khalid@balsam.test')->first();
        $nour   = User::where('email', 'patient.nour@balsam.test')->first();
        $tariq  = User::where('email', 'patient.tariq@balsam.test')->first();

        Patient::create([
            'user_id'       => $khalid->id,
            'full_name'     => 'Khalid Mansour',
            'phone'         => '+963-912-100001',
            'date_of_birth' => '1990-05-14',
            'gender'        => 'male',
        ]);

        Patient::create([
            'user_id'       => $nour->id,
            'full_name'     => 'Nour Abboud',
            'phone'         => '+963-912-100002',
            'date_of_birth' => '1995-08-22',
            'gender'        => 'female',
        ]);

        // Tariq — will have two past no_show appointments
        Patient::create([
            'user_id'       => $tariq->id,
            'full_name'     => 'Tariq Zein',
            'phone'         => '+963-912-100003',
            'date_of_birth' => '1988-01-30',
            'gender'        => 'male',
        ]);

        // Walk-in patient — no user account
        Patient::create([
            'user_id'       => null,
            'full_name'     => 'Hana Ibrahim',
            'phone'         => '+963-912-100004',
            'date_of_birth' => '2000-03-10',
            'gender'        => 'female',
        ]);

        // Another walk-in
        Patient::create([
            'user_id'       => null,
            'full_name'     => 'Youssef Qasim',
            'phone'         => '+963-912-100005',
            'date_of_birth' => '1975-11-05',
            'gender'        => 'male',
        ]);
    }
}
