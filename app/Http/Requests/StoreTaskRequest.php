<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status_id'   => ['required', 'integer', 'exists:statuses,id'],
            'priority'    => ['nullable', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'due_date'    => ['nullable', 'date'],
            'assignees'   => ['nullable', 'array'],
            'assignees.*' => ['exists:users,id'],
        ];
    }
}
