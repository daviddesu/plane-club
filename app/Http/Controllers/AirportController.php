<?php

namespace App\Http\Controllers;

use App\Models\Airport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use League\CommonMark\Extension\SmartPunct\EllipsesParser;

class AirportController extends Controller
{
    public function getAirportsSearch(Request $request): JsonResponse
    {
        $search = strtolower($request->get('search'));
        $airports = empty($search) ?
            Airport::all() :
            DB::table('airports')
            ->whereRaw("LOWER(name) LIKE '%$search%' OR LOWER(code) LIKE '%$search%'")
            ->get();


        return response()->json(
            array_map(function ($airport) {
                return ['id' => $airport->id, 'name' => "$airport->name ($airport->code)"];
            }, $airports->all())
        );
    }
}
