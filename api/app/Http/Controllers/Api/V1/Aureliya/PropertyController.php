<?php

namespace App\Http\Controllers\Api\V1\aureliya;

use App\Http\Controllers\Controller;
use App\Models\aureliya\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    // GET /properties
    // GET /properties (Now filters by country)
    public function index(Request $request)
    {
        $country = $request->query('country');

        // If no country is selected, return an empty list (to avoid loading 5000+ items)
        if (empty($country)) {
            return response()->json([]);
        }

        // Filter by the selected country
        return Property::with(['amenities'])
            ->where('country', $country)
            ->get();
    }

    // GET /countries (New endpoint to get list of unique countries)
    public function getCountries()
    {
        // Pluck unique countries from the database
        $countries = Property::distinct()->orderBy('country', 'asc')->pluck('country');
        return response()->json($countries);
    }

    // GET /properties/{id}
    public function show($id)
    {
        return Property::with(['amenities'])->findOrFail($id);
    }

    // Admin-only (disabled)
    public function store()
    {
        return response()->json(['error' => 'Forbidden'], 403);
    }
    public function update()
    {
        return response()->json(['error' => 'Forbidden'], 403);
    }
    public function destroy()
    {
        return response()->json(['error' => 'Forbidden'], 403);
    }
}
