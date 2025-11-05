<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CashierManageController extends Controller
{
    public function index()
    {
        // view: resources/views/kasir/manage.blade.php
        return view('kasir.manage');
    }
}
