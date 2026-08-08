<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Traits\AuditsActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordChangeController extends Controller
{
    use AuditsActions;

    public function show()
    {
        return view('auth.password-change');
    }

    public function update(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'different:current_password', Password::defaults()],
        ]);

        $userId = $request->user()->id;
        $request->user()->update([
            'password' => Hash::make($request->password),
            'needs_password_change' => false,
        ]);

        $this->audit($request, 'password.reset', \App\Models\User::class, $userId, null, null);

        return redirect()->route('admin.dashboard')->with('status', 'Password changed successfully.');
    }
}
