<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        return view('profile.show', [
            'user' => Auth::guard('web')->user()->load('employee'),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = Auth::guard('web')->user();

        $validated = $request->validated();

        $user->update([
            'name' => $validated['name'],
        ]);

        if (! empty($validated['password'])) {
            $user->update(['password' => $validated['password']]);
        }

        if ($user->employee) {
            $user->employee->update([
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'position' => $validated['position'] ?? $user->employee->position,
            ]);
        }

        return redirect()->route('profile.show')->with('success', 'Profil berhasil diperbarui.');
    }
}
