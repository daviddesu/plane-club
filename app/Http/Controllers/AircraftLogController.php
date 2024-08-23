<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AircraftLogController extends Controller
{
    public function index(): View
    {
        return view('aircraft_logs_gallery', []);
    }

    public function viewAircraftLog(string $id): View
    {
        return view('aircraft_log', ['id' => (int) $id]);
    }
}
