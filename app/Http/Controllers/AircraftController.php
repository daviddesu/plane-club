<?php

namespace App\Http\Controllers;

use App\Models\Aircraft;
use App\Models\Airport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AircraftController extends Controller
{
    public function getAircraftSearch(Request $request): JsonResponse
    {
        $search = strtolower($request->get('search'));

        if(empty($search)){
            return response()->json([]);
        }

        $searchTerms = explode(' ', strtolower($search));

        $aircraft = Aircraft::where(function($query) use ($searchTerms) {
            foreach ($searchTerms as $term) {
                $query->where(function($q) use ($term) {
                    $q->whereRaw("LOWER(manufacturer) LIKE ?", ["% {$term}%"])
                        ->orWhereRaw("LOWER(manufacturer) LIKE ?", ["{$term}%"])
                        ->orWhereRaw("LOWER(model) LIKE ?", ["% {$term}%"])
                        ->orWhereRaw("LOWER(model) LIKE ?", ["{$term}%"])
                        ->orWhereRaw("LOWER(varient) LIKE ?", ["% {$term}%"])
                        ->orWhereRaw("LOWER(varient) LIKE ?", ["{$term}%"]);
                });
            }
        })
        ->get();

        return response()->json(
            array_map(function ($plane) {
                return ['id' => $plane->id, 'name' => $plane->getFormattedName()];
            }, $aircraft->all())
        );
    }
}
