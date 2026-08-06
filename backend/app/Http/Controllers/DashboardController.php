<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function admin()
    {
        return view('dashboard.admin');
    }

    public function teacher()
    {
        return view('dashboard.teacher');
    }

    public function parent()
    {
        return view('dashboard.parent');
    }

    public function student()
    {
        return view('dashboard.student');
    }
}
