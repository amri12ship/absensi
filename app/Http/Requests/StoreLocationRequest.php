<?php

namespace App\Http\Requests;

use App\Models\AttendanceLocation;
use Illuminate\Foundation\Http\FormRequest;

class StoreLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('web')->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['required', 'integer', 'min:1', 'max:100000'],
            'status' => ['required', 'in:'.AttendanceLocation::STATUS_ACTIVE.','.AttendanceLocation::STATUS_INACTIVE],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lokasi wajib diisi.',
            'latitude.required' => 'Latitude wajib diisi.',
            'latitude.between' => 'Latitude harus antara -90 sampai 90.',
            'longitude.required' => 'Longitude wajib diisi.',
            'longitude.between' => 'Longitude harus antara -180 sampai 180.',
            'radius.required' => 'Radius wajib diisi.',
            'radius.min' => 'Radius harus lebih besar dari 0 meter.',
        ];
    }
}
