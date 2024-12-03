<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FavoriteController extends Controller
{
    public function addFavorite(Request $request, $wordId)
    {
        $guestId = $request->cookie('guest_id', Str::uuid()); // Generate a guest ID if not set

        Favorite::updateOrInsert([
            'word_id' => $wordId,
            'guest_id' => $guestId,
        ]);

        return response()->json(['message' => 'Favorite added!'])
            ->cookie('guest_id', $guestId, 60 * 24 * 30); // Store guest_id cookie for 30 days
    }

    public function removeFavorite(Request $request, $wordId)
    {
        $guestId = $request->cookie('guest_id');

        if ($guestId) {
            Favorite::where('word_id', $wordId)
                ->where('guest_id', $guestId)
                ->delete();

            return response()->json(['message' => 'Favorite removed!']);
        }

        return response()->json(['message' => 'Guest ID not found!'], 400);
    }

}
