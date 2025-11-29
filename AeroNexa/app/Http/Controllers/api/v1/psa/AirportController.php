<?php

namespace App\Http\Controllers\api\v1\psa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\psa\Airport;

class AirportController extends Controller
{
    // GET /psa/airports
    public function index()
    {
        return response()->json(Airport::all());
    }

    // GET /psa/airports/{id}
    public function show($id)
    {
        $airport = Airport::find($id);

        if (!$airport) {
            return response()->json(['message' => 'airport not found'], 404);
        }

        return response()->json($airport);
    }
}
