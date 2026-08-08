<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementsController extends Controller
{
    /**
     * Show announcements visible to a parent ("all" + "parent" target roles).
     */
    public function index(Request $request)
    {
        $announcements = Announcement::forRole('parent')
            ->with('createdBy')
            ->latest()
            ->paginate(15);

        return view('parent.announcements', compact('announcements'));
    }
}
