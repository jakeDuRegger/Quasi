<?php

use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\WordController;
use Illuminate\Support\Facades\Route;

/*
 * TODO: Create a way to track progress of the user's (without a login) 'learning'.
 * TODO: Have achievements... lol
 * TODO: Track a favorites list.
 */

Route::get('/', [WordController::class, 'show']);

Route::post('/favorites/{wordId}/add', [FavoriteController::class, 'addFavorite']);
Route::delete('/favorites/{wordId}/remove', [FavoriteController::class, 'removeFavorite']);
