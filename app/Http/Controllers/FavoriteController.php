<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Word;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FavoriteController extends Controller
{
    public function addFavorite($wordId)
    {
        $guestId = request()->cookie('guest_id', Str::uuid()); // Generate a guest ID if not set

        try {
            $word = Word::findOrFail($wordId); // Fetch word details

            Favorite::create([
                'word_id' => $wordId,
                'guest_id' => $guestId,
            ]);

            $updatedFavorites = $this->getFavorites(); // Fetch the updated favorites list

            return response()->json([
                'message' => 'Favorite added!',
                'word' => $word, // Include the word details
                'favorites' => $updatedFavorites, // Updated favorites list
            ])->cookie('guest_id', $guestId, 60 * 24 * 30); // Store guest_id cookie for 30 days
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while adding the favorite. Please try again later.',
            ], 500);
        }
    }

    public function removeFavorite($wordId)
    {
        $guestId = request()->cookie('guest_id');

        if ($guestId) {
            try {
                $deleted = Favorite::where('word_id', $wordId)
                    ->where('guest_id', $guestId)
                    ->delete();

                if ($deleted) {
                    $updatedFavorites = $this->getFavorites(); // Fetch the updated favorites list

                    return response()->json([
                        'message' => 'Favorite removed!',
                        'favorites' => $updatedFavorites, // Updated favorites list
                    ]);
                }

                return response()->json(['message' => 'Favorite not found!'], 404);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'An error occurred while removing the favorite. Please try again later.',
                ], 500);
            }
        }

        return response()->json(['message' => 'Guest ID not found!'], 400);
    }

    public function removeFavoritesList()
    {
        $guestId = request()->cookie('guest_id');

        if (!$guestId) {
            return response()->json(['message' => 'Guest ID not found!'], 400);
        }

        try {
            // Delete all favorites for the guest
            $deleted = Favorite::where('guest_id', $guestId)->delete();

            if ($deleted) {
                // Fetch the updated favorites list (should be empty now)
                return response()->json([
                    'message' => 'Favorites list culled!',
                    'favorites' => [], // Return an empty list
                ]);
            }

            return response()->json(['message' => 'No favorites found to delete.'], 404);
        } catch (\Exception $e) {

            return response()->json([
                'message' => 'An error occurred while removing the favorites. Please try again later.',
            ], 500);
        }

    }

    private function getFavorites()
    {
        $guestId = request()->cookie('guest_id');
        if ($guestId) {
            // Extract word IDs for the current guest
            $favoriteWordIds = Favorite::where('guest_id', $guestId)
                ->pluck('word_id') // Get a flat array of word IDs
                ->toArray();

            // Fetch corresponding words
            return Word::whereIn('id', $favoriteWordIds)
                ->select('id', 'name', 'frequency') // Only fetch necessary columns
                ->get()
                ->toArray();
        }

        return [];
    }

}
