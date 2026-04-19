<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectMemberRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $rules = [
            'project_role' => ['required', Rule::in(['admin', 'member', 'client'])],
        ];

        if ($this->input('_mode') === 'existing') {
            $rules['user_id'] = ['required', 'exists:users,id'];
        } else {
            $rules['email'] = ['required', 'email'];
        }

        return $rules;
    }
}
