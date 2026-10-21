<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ImportCredential;
use App\Models\User;
use App\Traits\AuditsActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'password' => ['required', 'confirmed', 'different:current_password', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $user = DB::transaction(function () use ($request): User {
            $user = User::query()->lockForUpdate()->findOrFail($request->user()->id);
            $user->update([
                'password' => Hash::make($request->password),
                'needs_password_change' => false,
            ]);

            ImportCredential::where('user_id', $user->id)->update([
                'password' => null,
                'temporary_password' => null,
            ]);

            $this->audit($request, 'password.reset', User::class, $user->id, null, null);

            return $user;
        });

        $redirectRoute = match ($user->role) {
            'admin' => 'admin.dashboard',
            'teacher' => 'teacher.dashboard',
            'student' => 'student.dashboard',
            'parent' => 'parent.dashboard',
            default => '/',
        };

        return redirect()->route($redirectRoute)->with('status', 'Password changed successfully.');
    }
}
