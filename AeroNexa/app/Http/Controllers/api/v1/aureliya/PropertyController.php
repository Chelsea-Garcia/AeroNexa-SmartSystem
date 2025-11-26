<?php

namespace App\Http\Controllers\Api\V1\aureliya;

use App\Http\Controllers\Controller;
use App\Models\aureliya\Property;

class PropertyController extends Controller
{
    // GET /properties
    public function index()
    {
        return Property::with(['amenities'])->get();
    }

    // GET /properties/{id}
    public function show($id)
    {
        $property = Property::with(['amenities'])->findOrFail($id);

        return $property;
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
