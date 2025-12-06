<?php

namespace App\Http\Controllers\api\v1\skyroute;

use App\Http\Controllers\Controller;
use App\Models\skyroute\Vehicle;
use App\Models\skyroute\Location;

class VehicleController extends Controller
{
    // GET /skyroute/vehicles/city/{city}
    public function vehiclesByCity($city)
    {
        // Try to treat $city as a Location ID first
        $location = Location::find($city);

        $locationIds = [];

        if ($location) {
            $locationIds[] = (string) $location->_id;
        } else {
            // Fallback: treat $city as a city name and find all matching locations
            $locations = Location::where('city', 'like', $city)->get();

            if ($locations->isEmpty()) {
                // Also try case-insensitive exact match (some Mongo drivers/collations differ)
                $locations = Location::where('city', $city)->get();
            }

            foreach ($locations as $loc) {
                $locationIds[] = (string) $loc->_id;
            }
        }

        if (empty($locationIds)) {
            return response()->json(['message' => 'Location not found'], 404);
        }

        $vehicles = Vehicle::whereIn('location_id', $locationIds)->get();

        if ($vehicles->isEmpty()) {
            return response()->json(['message' => 'No vehicles found for this location'], 404);
        }

        return response()->json($vehicles);
    }
}
