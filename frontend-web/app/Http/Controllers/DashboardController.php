<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function userDashboard() { return view('dashboard.user'); }
    public function adminDashboard() { return view('dashboard.admin'); }
}
