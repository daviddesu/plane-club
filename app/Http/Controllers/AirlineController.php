<?php

namespace App\Http\Controllers;

use App\Models\Airline;
use App\Models\Airport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AirlineController extends Controller
{
    public function getAirlinesSearch(Request $request): JsonResponse
    {
        $search = strtolower($request->get('search'));

        $selected = $request->get('selected');

        if($selected){
            $airline = Airline::find($selected)->first();
            return response()->json([['id' => $airline->id, 'name' => $airline->name]]);
        }

        if(empty($search)){
            return response()->json([]);
        }

        $searchTerms = explode(' ', strtolower($search));

        $airlines = DB::table('airlines')
            ->where(function($query) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $query->where(function($q) use ($term) {
                        $q->whereRaw("LOWER(name) LIKE ?", ["% {$term}%"])
                        ->orWhereRaw("LOWER(name) LIKE ?", ["{$term}%"]);
                    });
                }
            })
            ->get();

        return response()->json(
            array_map(function ($airline) {
                return ['id' => $airline->id, 'name' => $airline->name];
            }, $airlines->all())
        );
    }
}
