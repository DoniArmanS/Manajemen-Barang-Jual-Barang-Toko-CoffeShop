<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // Single-action controller: hanya render view
    public function __invoke()
    {
        // View kosong; data ringkasan akan diminta via fetch() dari JS
        return view('dashboard.index');
    }
}
