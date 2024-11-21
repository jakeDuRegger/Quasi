<?php

use App\Http\Controllers\WordController;
use Illuminate\Support\Facades\Route;

/*
 * TODO: Create a way to track progress of the user's (without a login) 'learning'.
 * TODO: Have achievements... lol
 * TODO: Track a favorites list.
 */

Route::get('/', [WordController::class, 'show']);
