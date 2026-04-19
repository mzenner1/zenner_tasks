<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttachmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'file'            => ['required', 'file', 'max:20480'], // 20MB max
            'attachable_type' => ['required', 'string', 'in:task,comment'],
            'attachable_id'   => ['required', 'string'],
        ];
    }
}
