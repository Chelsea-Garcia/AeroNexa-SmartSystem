<?php

namespace App\Http\Controllers\Api\V1\aureliya;

use App\Http\Controllers\Controller;
use App\Models\aureliya\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        return Review::all();
    }

    public function show($id)
    {
        return Review::findOrFail($id);
    }

    public function store(Request $request)
    {
        $review = Review::create([
            '_id'        => uuid_create(UUID_TYPE_RANDOM),
            'property_id' => $request->property_id,
            'user_id'    => $request->user_id,
            'rating'     => $request->rating,
            'comment'    => $request->comment,
        ]);

        return $review;
    }

    public function update(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        $review->update($request->only(['rating', 'comment']));

        return $review;
    }

    public function destroy()
    {
        return response()->json(['error' => 'Forbidden'], 403);
    }
}
