<?php

namespace App\Http\Controllers\Api\V1\aureliya;

use App\Http\Controllers\Controller;
use App\Models\aureliya\Amenity;

class AmenityController extends Controller
{
    public function index()
    {
        return Amenity::all();
    }

    public function show($id)
    {
        return Amenity::findOrFail($id);
    }

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
