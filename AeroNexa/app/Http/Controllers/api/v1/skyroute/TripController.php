<?php

namespace App\Http\Controllers\api\v1\skyroute;

use App\Http\Controllers\Controller;
use App\Models\skyroute\Trip;
use App\Models\skyroute\Route;
use App\Models\skyroute\Location;
use Illuminate\Http\Request;

class TripController extends Controller
{
    // --------------------------
    // LIST ALL TRIPS (with filters)
    // --------------------------
    public function index(Request $req)
    {
        $q = Trip::query();

        if ($req->type) {
            $q->whereHas('route', function ($r) use ($req) {
                $r->where('type', $req->type);
            });
        }

        if ($req->city) {
            $q->whereHas('route', function ($r) use ($req) {
                $r->where('origin_city', $req->city);
            });
        }

        return $q->get();
    }

    // --------------------------
    // TRIPS BY TYPE
    // --------------------------
    public function byType($type)
    {
        return Trip::whereHas(
            'route',
            fn($q) =>
            $q->where('type', $type)
        )->get();
    }

    // --------------------------
    // TRIPS BY CITY
    // --------------------------
    public function byCity($city)
    {
        return Trip::whereHas(
            'route',
            fn($q) =>
            $q->where('origin_city', $city)
        )->get();
    }

    // --------------------------
    // SHOW ONE TRIP
    // --------------------------
    public function show($id)
    {
        $trip = Trip::find($id);

        if (!$trip) {
            return response()->json(['error' => 'Trip not found'], 404);
        }

        return $trip;
    }
}
