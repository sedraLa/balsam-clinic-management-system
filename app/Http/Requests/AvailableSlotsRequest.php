<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AvailableSlotsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'date'      => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'branch_id.required'   => 'يجب تحديد الفرع.',
            'branch_id.exists'     => 'الفرع المحدد غير موجود.',
            'date.required'        => 'يجب تحديد التاريخ.',
            'date.date_format'     => 'صيغة التاريخ غير صحيحة. استخدم: Y-m-d',
            'date.after_or_equal'  => 'لا يمكن عرض مواعيد لتاريخ مضى.',
        ];
    }
}
