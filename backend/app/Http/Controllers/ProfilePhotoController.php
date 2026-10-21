<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfilePhotoController extends Controller
{
    public function update(Request $request)
    {
        abort_unless($request->user()->role !== 'student', 403, 'Student profile photos are managed by an administrator.');

        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        if (! $this->storePhoto($request->user(), $request->file('photo'))) {
            return back()->withErrors(['photo' => 'Unable to save the profile photo. Please try again.']);
        }

        return redirect()->route('settings.profile')
            ->with('status', 'Profile photo updated successfully.');
    }

    public function destroy(Request $request)
    {
        $this->removePhoto($request->user());

        return redirect()->route('settings.profile')
            ->with('status', 'Profile photo removed.');
    }

    public function updateForStudent(Request $request, Student $student)
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        if (! $this->storePhoto($student->user, $request->file('photo'))) {
            return back()->withErrors(['photo' => 'Unable to save the profile photo. Please try again.']);
        }

        return redirect()->route('admin.students.edit', $student)
            ->with('status', 'Student profile photo updated successfully.');
    }

    public function destroyForStudent(Student $student)
    {
        $this->removePhoto($student->user);

        return redirect()->route('admin.students.edit', $student)
            ->with('status', 'Student profile photo removed.');
    }

    protected function storePhoto($user, $file): bool
    {
        $disk = Storage::disk('public');
        $newPhotoPath = $disk->putFile('profile-photos', $file);

        if (! is_string($newPhotoPath) || $newPhotoPath === '') {
            return false;
        }

        $previousPhotoPath = $user->profile_photo;
        $user->profile_photo = $newPhotoPath;
        $user->saveOrFail();

        if ($this->isManagedProfilePhoto($previousPhotoPath) && $disk->exists($previousPhotoPath)) {
            $disk->delete($previousPhotoPath);
        }

        return true;
    }

    protected function removePhoto($user): void
    {
        if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        $user->profile_photo = null;
        $user->save();
    }

    private function isManagedProfilePhoto(?string $path): bool
    {
        if (! $path || ! str_starts_with($path, 'profile-photos/')) {
            return false;
        }

        $filename = substr($path, strlen('profile-photos/'));

        return $filename !== ''
            && basename($filename) === $filename
            && ! str_contains($filename, '\\');
    }
}
