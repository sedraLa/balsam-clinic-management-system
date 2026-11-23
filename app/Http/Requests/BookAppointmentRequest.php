<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class BookAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = $this->user()->role;

        return in_array($role, [
            UserRole::Admin,
            UserRole::Receptionist,
            UserRole::Patient,
        ]);
    }

    public function rules(): array
    {
        return [
            'doctor_id'  => ['required', 'integer', 'exists:doctors,id'],
            'patient_id' => ['required', 'integer', 'exists:patients,id'],
            'branch_id'  => ['required', 'integer', 'exists:branches,id'],
            'start_at'   => ['required', 'date_format:Y-m-d H:i:s'],
        ];
    }

    public function messages(): array
    {
        return [
            'doctor_id.required'  => 'يجب تحديد الطبيب.',
            'doctor_id.exists'    => 'الطبيب المحدد غير موجود.',
            'patient_id.required' => 'يجب تحديد المريض.',
            'patient_id.exists'   => 'المريض المحدد غير موجود.',
            'branch_id.required'  => 'يجب تحديد الفرع.',
            'branch_id.exists'    => 'الفرع المحدد غير موجود.',
            'start_at.required'   => 'يجب تحديد وقت الموعد.',
            'start_at.date_format' => 'صيغة وقت الموعد غير صحيحة. استخدم: Y-m-d H:i:s',
        ];
    }
}
