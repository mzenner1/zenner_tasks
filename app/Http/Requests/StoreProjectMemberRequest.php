<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectMemberRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        if ($this->input('_mode') === 'existing') {
            return ['user_id' => ['required', 'exists:users,id']];
        }
        return ['email' => ['required', 'email']];
    }
}
