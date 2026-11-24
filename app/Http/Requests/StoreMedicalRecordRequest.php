<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        $role = $this->user()->role;
        return in_array($role, [UserRole::Admin, UserRole::Doctor]);
    }

    public function rules(): array
    {
        return [
            'diagnosis'    => ['required', 'string', 'max:2000'],
            'notes'        => ['nullable', 'string', 'max:5000'],
            'prescription' => ['nullable', 'array'],
            'prescription.*.name'     => ['required_with:prescription', 'string'],
            'prescription.*.dosage'   => ['nullable', 'string'],
            'prescription.*.duration' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'diagnosis.required' => 'يجب إدخال التشخيص.',
            'diagnosis.max'      => 'التشخيص طويل جداً.',
            'prescription.array' => 'صيغة الوصفة الطبية غير صحيحة.',
            'prescription.*.name.required_with' => 'يجب إدخال اسم الدواء.',
        ];
    }
}
