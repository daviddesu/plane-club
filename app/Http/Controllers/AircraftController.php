<?php

namespace App\Http\Controllers;

use App\Models\Aircraft;
use App\Models\Airline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use League\CommonMark\Extension\SmartPunct\EllipsesParser;

class AircraftController extends Controller
{
    public function getAircraftSearch(Request $request): JsonResponse
    {
        $search = strtolower($request->get('search'));
        $aircraft = empty($search) ?
            Aircraft::all() :
            DB::table('aircraft')
            ->whereRaw("LOWER(manufacturer) LIKE '%$search%' OR LOWER(model) LIKE '%$search%' OR LOWER(varient) LIKE '%$search%'")
            ->get();


        return response()->json(
            array_map(function ($craft) {
                return ['id' => $craft->id, 'name' => "{$craft->manufacturer} {$craft->model}-{$craft->varient}"];
            }, $aircraft->all())
        );
    }
}
