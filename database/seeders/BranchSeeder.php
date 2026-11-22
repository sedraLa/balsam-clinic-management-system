<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            ['name' => 'Damascus',  'code' => 'DAM', 'address' => 'Al-Mazzeh, Damascus',    'phone' => '+963-11-1000001'],
            ['name' => 'Aleppo',    'code' => 'HAL', 'address' => 'Al-Aziziyeh, Aleppo',    'phone' => '+963-21-2000001'],
            ['name' => 'Latakia',   'code' => 'LAT', 'address' => 'Al-Corniche, Latakia',   'phone' => '+963-41-3000001'],
        ];

        foreach ($branches as $data) {
            Branch::create($data);
        }
    }
}
