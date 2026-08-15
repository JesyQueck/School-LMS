<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Traits\AuditsActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'target_role' => ['required', 'in:all,student,teacher,parent'],
        ]);

        $announcement = Announcement::create(array_merge($data, [
            'created_by' => Auth::id(),
        ]));

        $this->audit($request, 'announcement.created', Announcement::class, $announcement->id, null, $data);

        return redirect()->route('admin.announcements')->with('status', 'Announcement published.');
    }
}
