<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->index(['doctor_id', 'start_at'], 'appointments_doctor_start_index');
            $table->index(['branch_id', 'status'], 'appointments_branch_status_index');
            $table->index(['patient_id', 'status'], 'appointments_patient_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_doctor_start_index');
            $table->dropIndex('appointments_branch_status_index');
            $table->dropIndex('appointments_patient_status_index');
        });
    }
};
