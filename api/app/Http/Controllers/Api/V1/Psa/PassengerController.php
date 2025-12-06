<?php

namespace App\Http\Controllers\api\v1\psa;

use App\Http\Controllers\Controller;
use App\Models\psa\Passenger;
use Illuminate\Http\Request;

class PassengerController extends Controller
{
    public function store(Request $req)
    {
        $data = $req->validate([
            'user_id'                  => 'required|string',
            'first_name'               => 'required|string',
            'last_name'                => 'required|string',
            'gender'                   => 'required|string',
            'birthdate'                => 'required|date',
            'nationality'              => 'required|string',
            'passport_number'          => 'required|string',
            'passport_expiry'          => 'required|date',
            'special_assistance'       => 'nullable|string',
            'contact_number'           => 'required|string',
            'emergency_contact_name'   => 'required|string',
            'emergency_contact_number' => 'required|string',
        ]);

        $passenger = Passenger::create($data);

        return response()->json([
            'message' => 'Passenger created',
            'data'    => $passenger
        ]);
    }

    public function showByUser($user_id)
    {
        $passenger = Passenger::where('user_id', $user_id)->get();

        if ($passenger->isEmpty()) {
            return response()->json(['message' => 'Passenger not found'], 404);
        }

        return response()->json($passenger);
    }

    public function update(Request $req, $id)
    {
        $passenger = Passenger::where('_id', $id)->first();

        if (!$passenger) {
            return response()->json(['error' => 'Passenger not found'], 404);
        }

        $passenger->update($req->all());

        return response()->json([
            'message' => 'Passenger updated',
            'data'    => $passenger
        ]);
    }
}
