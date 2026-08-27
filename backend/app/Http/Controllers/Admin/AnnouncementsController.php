<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Traits\AuditsActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AnnouncementsController extends Controller
{
    use AuditsActions;

    public function index()
    {
        $announcements = Announcement::with('createdBy')->latest()->paginate(20);

        return view('admin.announcements.index', compact('announcements'));
    }

    public function create()
    {
        return view('admin.announcements.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'target_role' => ['required', 'in:all,student,teacher,parent'],
            'show_on_website' => ['boolean'],
            'is_event' => ['boolean'],
            'event_date' => ['nullable', 'date', 'required_if:is_event,1'],
            'event_time' => ['nullable', 'date_format:H:i', 'required_if:is_event,1'],
            'event_location' => ['nullable', 'string', 'max:255', 'required_if:is_event,1'],
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('announcements', 'public');
        }

        $announcement = Announcement::create(array_merge($data, [
            'created_by' => Auth::id(),
        ]));

        $this->audit($request, 'announcement.created', Announcement::class, $announcement->id, null, $data);

        return redirect()->route('admin.announcements')->with('status', 'Announcement published.');
    }

    public function edit(Announcement $announcement)
    {
        return view('admin.announcements.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'target_role' => ['required', 'in:all,student,teacher,parent'],
            'show_on_website' => ['boolean'],
            'is_event' => ['boolean'],
            'event_date' => ['nullable', 'date', 'required_if:is_event,1'],
            'event_time' => ['nullable', 'date_format:H:i', 'required_if:is_event,1'],
            'event_location' => ['nullable', 'string', 'max:255', 'required_if:is_event,1'],
        ]);

        if ($request->hasFile('image')) {
            if ($announcement->image) {
                Storage::disk('public')->delete($announcement->image);
            }
            $data['image'] = $request->file('image')->store('announcements', 'public');
        }

        if ($request->boolean('clear_image')) {
            if ($announcement->image) {
                Storage::disk('public')->delete($announcement->image);
            }
            $data['image'] = null;
        }

        $announcement->update($data);

        $this->audit($request, 'announcement.updated', Announcement::class, $announcement->id, null, $data);

        return redirect()->route('admin.announcements')->with('status', 'Announcement updated.');
    }

    public function destroy(Request $request, Announcement $announcement)
    {
        if ($announcement->image) {
            Storage::disk('public')->delete($announcement->image);
        }

        $this->audit($request, 'announcement.deleted', Announcement::class, $announcement->id);

        $announcement->delete();

        return redirect()->route('admin.announcements')->with('status', 'Announcement deleted.');
    }
}
