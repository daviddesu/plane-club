<?php

namespace App\Http\Controllers;

use App\Models\Airline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use League\CommonMark\Extension\SmartPunct\EllipsesParser;

class AirlineController extends Controller
{
    public function getAirlinesSearch(Request $request): JsonResponse
    {
        $search = strtolower($request->get('search'));
        $airlines = empty($search) ?
            Airline::all() :
            DB::table('airlines')
            ->whereRaw("LOWER(name) LIKE '%$search%'")
            ->get();


        return response()->json(
            array_map(function ($airline) {
                return ['id' => $airline->id, 'name' => "$airline->name"];
            }, $airlines->all())
        );
    }
}
