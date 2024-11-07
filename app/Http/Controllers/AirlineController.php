<?php

namespace App\Http\Controllers;

use App\Models\Airport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AirlineController extends Controller
{
    public function getAirlinesSearch(Request $request): JsonResponse
    {
        $search = strtolower($request->get('search'));

        if(empty($search)){
            return [];
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
