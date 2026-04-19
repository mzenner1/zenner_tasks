<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttachmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'files'           => ['required', 'array', 'min:1'],
            'files.*'         => ['file', 'max:20480'],
            'attachable_type' => ['required', 'string', 'in:task,comment'],
            'attachable_id'   => ['required', 'string'],
        ];
    }
}
