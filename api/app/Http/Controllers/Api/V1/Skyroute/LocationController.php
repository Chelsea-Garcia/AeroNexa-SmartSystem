<?php

namespace App\Http\Controllers\api\v1\skyroute;

use App\Http\Controllers\Controller;
use App\Models\skyroute\Location;

class LocationController extends Controller
{
    // GET /skyroute/locations
    public function index()
    {
        return response()->json(Location::all());
    }

    // GET /skyroute/locations/{id}
    public function show($id)
    {
        $location = Location::find($id);

        if (!$location) {
            return response()->json(['message' => 'Location not found'], 404);
        }

        return response()->json($location);
    }
}
