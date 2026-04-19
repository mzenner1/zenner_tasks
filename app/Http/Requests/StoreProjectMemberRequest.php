<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectMemberRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'email'        => ['required', 'email'],
            'project_role' => ['required', Rule::in(['admin', 'member', 'client'])],
        ];
    }
}
