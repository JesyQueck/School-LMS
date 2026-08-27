<?php

namespace App\Http\Controllers;

use App\Models\Announcement;

class PublicController extends Controller
{
    public function home()
    {
        $announcements = Announcement::where(function ($q) {
            $q->where('target_role', 'all')
                ->orWhere('show_on_website', true);
        })
            ->with('createdBy')
            ->latest()
            ->limit(3)
            ->get();

        $events = Announcement::where('is_event', true)
            ->where('event_date', '>=', now()->toDateString())
            ->where(function ($q) {
                $q->where('target_role', 'all')
                    ->orWhere('show_on_website', true);
            })
            ->with('createdBy')
            ->orderBy('event_date')
            ->limit(5)
            ->get();

        return view('public.home', compact('announcements', 'events'));
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
        $announcements = Announcement::where('target_role', 'all')
            ->orWhere('show_on_website', true)
            ->with('createdBy')
            ->latest()
            ->paginate(15);

        return view('public.announcements', compact('announcements'));
    }

    public function academics()
    {
        return view('public.academics');
    }

    public function gallery()
    {
        return view('public.gallery');
    }

    public function news()
    {
        return view('public.news');
    }

    public function faq()
    {
        return view('public.faq');
    }

    public function privacy()
    {
        return view('public.privacy');
    }

    public function terms()
    {
        return view('public.terms');
    }
}
