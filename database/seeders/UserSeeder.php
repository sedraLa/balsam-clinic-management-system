<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $dam = Branch::where('code', 'DAM')->first();
        $hal = Branch::where('code', 'HAL')->first();
        $lat = Branch::where('code', 'LAT')->first();

        // Admin
        User::create([
            'name'      => 'Admin User',
            'email'     => 'admin@balsam.test',
            'password'  => Hash::make('password'),
            'role'      => UserRole::Admin,
            'branch_id' => null,
        ]);

        // Receptionists
        User::create([
            'name'      => 'Rima Khalil',
            'email'     => 'receptionist.dam@balsam.test',
            'password'  => Hash::make('password'),
            'role'      => UserRole::Receptionist,
            'branch_id' => $dam->id,
        ]);

        User::create([
            'name'      => 'Sara Haddad',
            'email'     => 'receptionist.hal@balsam.test',
            'password'  => Hash::make('password'),
            'role'      => UserRole::Receptionist,
            'branch_id' => $hal->id,
        ]);

        // Doctors
        User::create([
            'name'      => 'Dr. Ahmad Karimi',
            'email'     => 'doctor.karimi@balsam.test',
            'password'  => Hash::make('password'),
            'role'      => UserRole::Doctor,
            'branch_id' => $dam->id,
        ]);

        User::create([
            'name'      => 'Dr. Lina Nassar',
            'email'     => 'doctor.nassar@balsam.test',
            'password'  => Hash::make('password'),
            'role'      => UserRole::Doctor,
            'branch_id' => $hal->id,
        ]);

        User::create([
            'name'      => 'Dr. Omar Fayyad',
            'email'     => 'doctor.fayyad@balsam.test',
            'password'  => Hash::make('password'),
            'role'      => UserRole::Doctor,
            'branch_id' => $dam->id,
        ]);

        // Patients with user accounts
        User::create([
            'name'      => 'Khalid Mansour',
            'email'     => 'patient.khalid@balsam.test',
            'password'  => Hash::make('password'),
            'role'      => UserRole::Patient,
            'branch_id' => null,
        ]);

        User::create([
            'name'      => 'Nour Abboud',
            'email'     => 'patient.nour@balsam.test',
            'password'  => Hash::make('password'),
            'role'      => UserRole::Patient,
            'branch_id' => null,
        ]);

        // Patient who will have no-show history (needs user account for token testing too)
        User::create([
            'name'      => 'Tariq Zein',
            'email'     => 'patient.tariq@balsam.test',
            'password'  => Hash::make('password'),
            'role'      => UserRole::Patient,
            'branch_id' => null,
        ]);
    }
}
