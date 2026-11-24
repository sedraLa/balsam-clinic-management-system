<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240',
                'mimes:jpg,jpeg,png,pdf,doc,docx',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'يجب اختيار ملف.',
            'file.max'      => 'حجم الملف يتجاوز الحد المسموح به (10 ميغابايت).',
            'file.mimes'    => 'نوع الملف غير مسموح به. الأنواع المقبولة: jpg, jpeg, png, pdf, doc, docx.',
        ];
    }
}
