<?php

namespace App\Http\Requests;

use App\Models\Holiday;
use Illuminate\Foundation\Http\FormRequest;

class StoreHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('web')->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date', 'unique:holidays,date'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:'.Holiday::STATUS_ACTIVE.','.Holiday::STATUS_INACTIVE],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama hari libur wajib diisi.',
            'date.required' => 'Tanggal hari libur wajib diisi.',
            'date.unique' => 'Sudah ada hari libur pada tanggal tersebut.',
            'status.required' => 'Status hari libur wajib diisi.',
        ];
    }
}