<?php

namespace App\Http\Controllers\api\v1\trutravel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\trutravel\Package;

class PackageController extends Controller
{
    // GET /trutravel/packages
    public function index()
    {
        return response()->json(Package::all());
    }

    // GET /trutravel/packages/{id}
    public function show($id)
    {
        $package = Package::where('_id', $id)->get();

        if (!$package) {
            return response()->json(['message' => 'package not found'], 404);
        }

        return response()->json($package);
    }
}
