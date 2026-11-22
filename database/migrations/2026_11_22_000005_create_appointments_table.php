<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('doctor_id')->constrained('doctors')->restrictOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->restrictOnDelete();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('status', 20)->default('booked');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('doctor_id');
            $table->index('patient_id');
            $table->index('branch_id');
            $table->index('status');
            $table->index('start_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
