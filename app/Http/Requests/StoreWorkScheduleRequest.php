<?php

namespace App\Http\Requests;

use App\Models\WorkSchedule;
use Illuminate\Foundation\Http\FormRequest;

class StoreWorkScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('web')->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'time_in' => ['required', 'date_format:H:i'],
            'time_out' => ['required', 'date_format:H:i', 'after:time_in'],
            'tolerance_minutes' => ['required', 'integer', 'min:0', 'max:600'],
            'status' => ['required', 'in:'.WorkSchedule::STATUS_ACTIVE.','.WorkSchedule::STATUS_INACTIVE],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama jadwal wajib diisi.',
            'time_in.required' => 'Jam masuk wajib diisi.',
            'time_in.date_format' => 'Format jam masuk tidak valid (contoh: 08:00).',
            'time_out.required' => 'Jam keluar wajib diisi.',
            'time_out.date_format' => 'Format jam keluar tidak valid (contoh: 17:00).',
            'time_out.after' => 'Jam keluar harus setelah jam masuk.',
            'tolerance_minutes.required' => 'Toleransi keterlambatan wajib diisi.',
            'tolerance_minutes.min' => 'Toleransi tidak boleh kurang dari 0 menit.',
        ];
    }
}