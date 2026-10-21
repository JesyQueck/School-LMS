<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\User;
use App\Traits\AuditsActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    use AuditsActions;

    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255']]);
        $email = mb_strtolower(trim($data['email']));
        $parent = User::query()
            ->where('role', 'parent')
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if ($parent) {
            PasswordBroker::sendResetLink(['email' => $parent->email]);
        }

        return back()->with('status', 'If an eligible parent account exists for that email address, a password reset link will be sent.');
    }

    public function showResetForm(string $token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function reset(ResetPasswordRequest $request)
    {
        $data = $request->validated();
        $email = mb_strtolower(trim($data['email']));
        $parent = User::query()
            ->where('role', 'parent')
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (! $parent) {
            return back()->withErrors(['email' => 'The password reset link is invalid or has expired.']);
        }

        $resetCompleted = false;
        $status = PasswordBroker::reset([
            ...$request->only('password', 'password_confirmation', 'token'),
            'email' => $parent->email,
        ], function (User $user, string $password) use ($request, &$resetCompleted): void {
            if ($user->role !== 'parent') {
                return;
            }

            $user->forceFill([
                'password' => Hash::make($password),
                'needs_password_change' => false,
                'remember_token' => Str::random(60),
            ])->save();

            $this->audit($request, 'parent.password_reset', User::class, $user->id, null, [
                'role' => 'parent',
            ]);
            $resetCompleted = true;
        });

        return $status === PasswordBroker::PASSWORD_RESET && $resetCompleted
            ? redirect('/login')->with('status', 'Your password has been reset successfully.')
            : back()->withErrors(['email' => 'The password reset link is invalid or has expired.']);
    }
}
