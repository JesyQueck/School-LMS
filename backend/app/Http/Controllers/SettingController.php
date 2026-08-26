<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function profile()
    {
        return view('settings.profile');
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        abort_unless($user->role !== 'student', 403, 'Student profiles are managed by an administrator.');

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $user->update([
            'name' => $request->input('name'),
        ]);

        return redirect()->route('settings.profile')
            ->with('status', 'Display name updated successfully.');
    }
}
