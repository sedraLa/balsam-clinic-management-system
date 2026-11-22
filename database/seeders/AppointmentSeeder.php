<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\AppointmentSequence;
use App\Models\Branch;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        $dam = Branch::where('code', 'DAM')->first();
        $hal = Branch::where('code', 'HAL')->first();

        $karimi = Doctor::whereHas('user', fn($q) => $q->where('email', 'doctor.karimi@balsam.test'))->first();
        $nassar = Doctor::whereHas('user', fn($q) => $q->where('email', 'doctor.nassar@balsam.test'))->first();
        $fayyad = Doctor::whereHas('user', fn($q) => $q->where('email', 'doctor.fayyad@balsam.test'))->first();

        $khalid = Patient::whereHas('user', fn($q) => $q->where('email', 'patient.khalid@balsam.test'))->first();
        $nour   = Patient::whereHas('user', fn($q) => $q->where('email', 'patient.nour@balsam.test'))->first();
        $tariq  = Patient::whereHas('user', fn($q) => $q->where('email', 'patient.tariq@balsam.test'))->first();
        $hana   = Patient::where('full_name', 'Hana Ibrahim')->first();

        $receptionist = User::where('email', 'receptionist.dam@balsam.test')->first();

        $tomorrow = now()->addDay()->format('Y-m-d');
        $year     = now()->year;

        // ── DAM appointments ────────────────────────────────────────────────

        // DAM-2026-0001: tomorrow, booked
        Appointment::create([
            'reference'  => 'DAM-' . $year . '-0001',
            'doctor_id'  => $karimi->id,
            'patient_id' => $khalid->id,
            'branch_id'  => $dam->id,
            'start_at'   => $tomorrow . ' 09:00:00',
            'end_at'     => $tomorrow . ' 09:20:00',
            'status'     => AppointmentStatus::Booked,
            'created_by' => $receptionist->id,
        ]);

        // DAM-2026-0002: tomorrow, booked
        Appointment::create([
            'reference'  => 'DAM-' . $year . '-0002',
            'doctor_id'  => $fayyad->id,
            'patient_id' => $nour->id,
            'branch_id'  => $dam->id,
            'start_at'   => $tomorrow . ' 11:00:00',
            'end_at'     => $tomorrow . ' 11:30:00',
            'status'     => AppointmentStatus::Booked,
            'created_by' => $receptionist->id,
        ]);

        // DAM-2026-0003: past, booked but not arrived (no_show candidate)
        Appointment::create([
            'reference'  => 'DAM-' . $year . '-0003',
            'doctor_id'  => $karimi->id,
            'patient_id' => $tariq->id,
            'branch_id'  => $dam->id,
            'start_at'   => now()->subDays(3)->format('Y-m-d') . ' 09:00:00',
            'end_at'     => now()->subDays(3)->format('Y-m-d') . ' 09:20:00',
            'status'     => AppointmentStatus::Booked,
            'created_by' => $receptionist->id,
        ]);

        // DAM-2026-0004: past no_show for Tariq (first historical no-show)
        Appointment::create([
            'reference'  => 'DAM-' . $year . '-0004',
            'doctor_id'  => $fayyad->id,
            'patient_id' => $tariq->id,
            'branch_id'  => $dam->id,
            'start_at'   => now()->subDays(10)->format('Y-m-d') . ' 11:00:00',
            'end_at'     => now()->subDays(10)->format('Y-m-d') . ' 11:30:00',
            'status'     => AppointmentStatus::NoShow,
            'created_by' => $receptionist->id,
        ]);

        // ── HAL appointments ────────────────────────────────────────────────

        $receptionistHal = User::where('email', 'receptionist.hal@balsam.test')->first();

        // HAL-2026-0001: tomorrow, booked
        Appointment::create([
            'reference'  => 'HAL-' . $year . '-0001',
            'doctor_id'  => $nassar->id,
            'patient_id' => $hana->id,
            'branch_id'  => $hal->id,
            'start_at'   => $tomorrow . ' 08:00:00',
            'end_at'     => $tomorrow . ' 08:15:00',
            'status'     => AppointmentStatus::Booked,
            'created_by' => $receptionistHal->id,
        ]);

        // HAL-2026-0002: past, booked but not arrived (no_show candidate) for Tariq
        Appointment::create([
            'reference'  => 'HAL-' . $year . '-0002',
            'doctor_id'  => $nassar->id,
            'patient_id' => $tariq->id,
            'branch_id'  => $hal->id,
            'start_at'   => now()->subDays(5)->format('Y-m-d') . ' 08:00:00',
            'end_at'     => now()->subDays(5)->format('Y-m-d') . ' 08:15:00',
            'status'     => AppointmentStatus::Booked,
            'created_by' => $receptionistHal->id,
        ]);

        // ── Sequences ───────────────────────────────────────────────────────

        AppointmentSequence::create([
            'branch_id'   => $dam->id,
            'year'        => $year,
            'last_number' => 4,
        ]);

        AppointmentSequence::create([
            'branch_id'   => $hal->id,
            'year'        => $year,
            'last_number' => 2,
        ]);
    }
}
