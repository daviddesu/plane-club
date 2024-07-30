<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AircraftLogController extends Controller
{
    public function index(): View
    {
        return view('aircraft_logs', []);
    }
}
