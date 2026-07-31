<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function home()
    {
        $announcements = Announcement::forRole('all')
            ->with('createdBy')
            ->latest()
            ->limit(3)
            ->get();

        return view('public.home', compact('announcements'));
    }

    public function about()
    {
        return view('public.about');
    }

    public function contact()
    {
        return view('public.contact');
    }

    public function admissions()
    {
        return view('public.admissions');
    }

    public function announcements()
    {
        $announcements = Announcement::forRole('all')
            ->with('createdBy')
            ->latest()
            ->paginate(15);

        return view('public.announcements', compact('announcements'));
    }
}
