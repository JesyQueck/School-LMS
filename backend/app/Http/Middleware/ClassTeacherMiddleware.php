<?php

namespace App\Http\Middleware;

use App\Models\ClassAssignment;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClassTeacherMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'teacher') {
            abort(403, 'Teacher access required.');
        }

        $teacher = $user->teacher;

        if (! $teacher) {
            abort(403, 'Teacher profile not found.');
        }

        $isClassTeacher = ClassAssignment::where('teacher_id', $teacher->id)
            ->whereHas('academicSession', function ($query) {
                $query->where('is_current', true);
            })
            ->exists();

        if (! $isClassTeacher) {
            abort(403, 'This action is only available for class teachers.');
        }

        return $next($request);
    }
}