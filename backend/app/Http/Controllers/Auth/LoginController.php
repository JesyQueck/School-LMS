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
            'bursar' => '/bursar/dashboard',
            'teacher' => '/teacher/dashboard',
            'parent' => '/parent/dashboard',
            'student' => '/student/dashboard',
            default => '/dashboard',
        };
    }

protected function resolveUser(string $identifier): ?User
{
    $identifier = mb_strtolower(trim($identifier));

    $users = User::query()
        ->where(function ($query) use ($identifier) {

            // Admin: email
            $query->where(function ($q) use ($identifier) {
                $q->where('role', 'admin')
                    ->whereRaw('LOWER(email) = ?', [$identifier]);
            })

            // Teacher and Bursar: shared Staff ID
            ->orWhere(function ($q) use ($identifier) {
                $q->whereIn('role', ['teacher', 'bursar'])
                    ->whereRaw('LOWER(staff_id) = ?', [$identifier]);
            })

            // Student: admission number
            ->orWhere(function ($q) use ($identifier) {
                $q->where('role', 'student')
                    ->whereHas('student', function ($student) use ($identifier) {
                        $student->whereRaw('LOWER(admission_no) = ?', [$identifier]);
                    });
            })

            // Parent: email or PARENT-{id}
            ->orWhere(function ($q) use ($identifier) {
                $q->where('role', 'parent')
                    ->where(function ($parentQuery) use ($identifier) {
                        $parentQuery->whereRaw('LOWER(email) = ?', [$identifier]);

                        if (preg_match('/^parent-([1-9]\d*)$/i', $identifier, $matches)) {
                            $parentQuery->orWhereHas('parentProfile', function ($parent) use ($matches) {
                                $parent->whereKey((int) $matches[1]);
                            });
                        }
                    });
            });
        })
        ->limit(2)
        ->get();

    return $users->count() === 1 ? $users->first() : null;
}
}
