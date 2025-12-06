<?php

namespace App\Http\Controllers\api\v1\psa;

use App\Http\Controllers\Controller;
use App\Models\psa\Flight;
use Illuminate\Http\Request;

class FlightController extends Controller
{
    public function index()
    {
        return Flight::all();
    }

    public function show($id)
    {
        $flight = Flight::find($id);

        if (!$flight) {
            return response()->json(['error' => 'Flight not found'], 404);
        }

        return $flight;
    }

    // SEARCH flights by origin, destination, date
    public function search(Request $request)
    {
        $request->validate([
            'origin'      => 'nullable|string',
            'destination' => 'nullable|string',
        ]);

        $query = Flight::query();

        if ($request->origin) {
            $query->where('origin', $request->origin);
        }

        if ($request->destination) {
            $query->where('destination', $request->destination);
        }

        return response()->json($query->get());
    }
}
