<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('web')->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $userId = $this->route('employee')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'nik' => ['required', 'string', 'max:30', Rule::unique('employees', 'nik')->ignore($this->route('employee')?->employee?->id ?? null)->whereNull('deleted_at')],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'position' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:'.User::STATUS_ACTIVE.','.User::STATUS_INACTIVE],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.unique' => 'Email sudah digunakan.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'nik.unique' => 'NIK sudah digunakan.',
            'position.required' => 'Jabatan wajib diisi.',
        ];
    }
}
