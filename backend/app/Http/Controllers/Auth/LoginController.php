<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\AuditsActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuditsActions;

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $identifierField = $request->has('identifier') ? 'identifier' : 'email';
        $credentials = $request->validate([
            $identifierField => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $identifier = mb_strtolower(trim($credentials[$identifierField]));
        $user = $this->resolveUser($identifier);
        $passwordMatches = $user && Hash::check($credentials['password'], $user->password);

        if (! $passwordMatches || ! $user->is_active) {
            if ($passwordMatches) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            throw ValidationException::withMessages([
                $identifierField => __('auth.failed'),
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $this->audit($request, 'user.login', User::class, $user->id);

        if ($user->needs_password_change) {
            return redirect()->route('password.change');
        }

        return redirect()->intended($this->redirectTo($user));
    }

    public function logout(Request $request)
    {
        $this->audit($request, 'user.logout', User::class, $request->user()->id);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    protected function redirectTo(User $user): string
    {
        return match ($user->role) {
            'admin' => '/admin/dashboard',
            'teacher' => '/teacher/dashboard',
            'parent' => '/parent/dashboard',
            'student' => '/student/dashboard',
            default => '/dashboard',
        };
    }

    protected function resolveUser(string $identifier): ?User
    {
        $users = User::query()
            ->where(function ($query) use ($identifier) {
                $query->where(function ($query) use ($identifier) {
                    $query->whereIn('role', ['admin', 'teacher', 'parent'])
                        ->whereRaw('LOWER(email) = ?', [$identifier]);
                })->orWhere(function ($query) use ($identifier) {
                    $query->where('role', 'teacher')
                        ->whereHas('teacher', fn ($teacher) => $teacher
                            ->whereRaw('LOWER(employee_id) = ?', [$identifier]));
                })->orWhere(function ($query) use ($identifier) {
                    $query->where('role', 'student')
                        ->whereHas('student', fn ($student) => $student
                            ->whereRaw('LOWER(admission_no) = ?', [$identifier]));
                });

                if (preg_match('/^parent-([1-9]\d*)$/i', $identifier, $matches)) {
                    $query->orWhere(function ($query) use ($matches) {
                        $query->where('role', 'parent')
                            ->whereHas('parentProfile', fn ($parent) => $parent->whereKey((int) $matches[1]));
                    });
                }
            })
            ->limit(2)
            ->get();

        return $users->count() === 1 ? $users->first() : null;
    }
}
