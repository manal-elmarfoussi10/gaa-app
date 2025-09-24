<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        // gate/permission check if needed
        return true;
    }

    public function rules(): array
    {
        // base company rules
        $rules = [
            'name'  => ['required','string','max:255'],
            'email' => ['nullable','email','max:255'],
            'phone' => ['nullable','string','max:50'],

            'create_admin' => ['sometimes','boolean'],
        ];

        // when the toggle “create first user” is on, validate nested admin payload
        if ($this->boolean('create_admin')) {
            $roles = implode(',', array_keys(collect(User::roles())->except(User::ROLE_SUPERADMIN)->toArray()));

            $rules = array_merge($rules, [
                'admin.first_name' => ['required','string','max:255'],
                'admin.last_name'  => ['required','string','max:255'],
                'admin.email'      => ['required','email','max:255','unique:users,email'],
                'admin.role'       => ['required',"in:$roles"],
                // IMPORTANT: confirmed requires a field named admin[password_confirmation]
                'admin.password'   => ['required','string','min:8','confirmed'],
                'admin.is_active'  => ['sometimes','boolean'],
            ]);
        }

        return $rules;
    }
}