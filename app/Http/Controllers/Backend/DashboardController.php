<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Routing\Controller as BaseController;

class DashboardController extends BaseController
{
    public function __construct()
    {
        $this->middleware('permission:dashboard view')->only(['index']);
    }

    public function index()
    {
        return view('backend.dashboard');
    }
}
