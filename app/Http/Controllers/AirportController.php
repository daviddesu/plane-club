<?php

namespace App\Http\Controllers;

use App\Models\Airport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AirportController extends Controller
{
    public function getAirportsSearch(Request $request): JsonResponse
    {
        $search = strtolower($request->get('search'));

        if(empty($search)){
            return [];
        }

        $searchTerms = explode(' ', strtolower($search));

        $airports = DB::table('airports')
            ->where(function($query) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $query->where(function($q) use ($term) {
                        $q->whereRaw("LOWER(name) LIKE ?", ["% {$term}%"])
                        ->orWhereRaw("LOWER(name) LIKE ?", ["{$term}%"])
                        ->orWhereRaw("LOWER(iata_code) LIKE ?", ["{$term}%"]);
                    });
                }
            })
            ->get();

        return response()->json(
            array_map(function ($airport) {
                return ['id' => $airport->id, 'name' => "$airport->name ($airport->iata_code)"];
            }, $airports->all())
        );
    }
}
