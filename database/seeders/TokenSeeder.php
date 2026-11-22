<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TokenSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            'admin@balsam.test',
            'receptionist.dam@balsam.test',
            'receptionist.hal@balsam.test',
            'doctor.karimi@balsam.test',
            'doctor.nassar@balsam.test',
            'doctor.fayyad@balsam.test',
            'patient.khalid@balsam.test',
            'patient.nour@balsam.test',
            'patient.tariq@balsam.test',
        ];

        $lines = ["Balsam Demo API Tokens\n", str_repeat('=', 60) . "\n"];

        foreach ($accounts as $email) {
            $user  = User::where('email', $email)->first();
            $token = $user->createToken('demo')->plainTextToken;
            $lines[] = sprintf("%-42s %s\n", $email, $token);
        }

        file_put_contents(storage_path('demo-tokens.txt'), implode('', $lines));

        $this->command->info('Demo tokens written to storage/demo-tokens.txt');
    }
}
