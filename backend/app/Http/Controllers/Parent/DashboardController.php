<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $parent = $request->user()->parentProfile;

        $children = $parent
            ? $parent->students()->with('class')->get()
            : collect();

        $announcements = Announcement::forRole('parent')
            ->with('createdBy')
            ->latest()
            ->limit(5)
            ->get();

        return view('parent.dashboard', compact('children', 'announcements'));
    }
}
