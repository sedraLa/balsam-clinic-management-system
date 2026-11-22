<?php

namespace Database\Seeders;

use App\Enums\WeekDay;
use App\Models\Branch;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Seeder;

class DoctorScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $dam = Branch::where('code', 'DAM')->first();
        $hal = Branch::where('code', 'HAL')->first();
        $lat = Branch::where('code', 'LAT')->first();

        $karimi = Doctor::whereHas('user', fn($q) => $q->where('email', 'doctor.karimi@balsam.test'))->first();
        $nassar = Doctor::whereHas('user', fn($q) => $q->where('email', 'doctor.nassar@balsam.test'))->first();
        $fayyad = Doctor::whereHas('user', fn($q) => $q->where('email', 'doctor.fayyad@balsam.test'))->first();

        // Dr. Karimi — Damascus (Sun–Tue morning) and Aleppo (Wed–Thu morning)
        $karimiDamDays = [WeekDay::Sunday, WeekDay::Monday, WeekDay::Tuesday];
        foreach ($karimiDamDays as $day) {
            $karimi->schedules()->create([
                'branch_id'     => $dam->id,
                'day_of_week'   => $day,
                'start_time'    => '09:00',
                'end_time'      => '13:00',
                'slot_duration' => 20,
                'is_active'     => true,
            ]);
        }

        $karimiHalDays = [WeekDay::Wednesday, WeekDay::Thursday];
        foreach ($karimiHalDays as $day) {
            $karimi->schedules()->create([
                'branch_id'     => $hal->id,
                'day_of_week'   => $day,
                'start_time'    => '10:00',
                'end_time'      => '14:00',
                'slot_duration' => 20,
                'is_active'     => true,
            ]);
        }

        // Dr. Nassar — Aleppo (Sun–Thu) with two shifts
        $nassarDays = [WeekDay::Sunday, WeekDay::Monday, WeekDay::Tuesday, WeekDay::Wednesday, WeekDay::Thursday];
        foreach ($nassarDays as $day) {
            // Morning shift
            $nassar->schedules()->create([
                'branch_id'     => $hal->id,
                'day_of_week'   => $day,
                'start_time'    => '08:00',
                'end_time'      => '12:00',
                'slot_duration' => 15,
                'is_active'     => true,
            ]);
            // Evening shift
            $nassar->schedules()->create([
                'branch_id'     => $hal->id,
                'day_of_week'   => $day,
                'start_time'    => '17:00',
                'end_time'      => '20:00',
                'slot_duration' => 15,
                'is_active'     => true,
            ]);
        }

        // Dr. Fayyad — Damascus (Mon–Thu) and Latakia (Sat)
        $fayyadDamDays = [WeekDay::Monday, WeekDay::Tuesday, WeekDay::Wednesday, WeekDay::Thursday];
        foreach ($fayyadDamDays as $day) {
            $fayyad->schedules()->create([
                'branch_id'     => $dam->id,
                'day_of_week'   => $day,
                'start_time'    => '11:00',
                'end_time'      => '15:00',
                'slot_duration' => 30,
                'is_active'     => true,
            ]);
        }

        $fayyad->schedules()->create([
            'branch_id'     => $lat->id,
            'day_of_week'   => WeekDay::Saturday,
            'start_time'    => '09:00',
            'end_time'      => '13:00',
            'slot_duration' => 30,
            'is_active'     => true,
        ]);
    }
}
