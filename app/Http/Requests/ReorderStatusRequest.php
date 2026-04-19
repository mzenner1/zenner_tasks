<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderStatusRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'statuses'             => ['required', 'array'],
            'statuses.*.id'        => ['required', 'integer', 'exists:statuses,id'],
            'statuses.*.sort_order'=> ['required', 'integer', 'min:0'],
        ];
    }
}
