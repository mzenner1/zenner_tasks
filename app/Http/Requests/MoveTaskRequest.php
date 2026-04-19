<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MoveTaskRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'status_id'  => ['required', 'integer', 'exists:statuses,id'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ];
    }
}
