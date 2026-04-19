<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'body'          => ['required', 'string'],
            'is_internal'   => ['nullable', 'boolean'],
            'parent_id'     => ['nullable', 'exists:comments,id'],
            'attachments'   => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:20480'],
        ];
    }
}
