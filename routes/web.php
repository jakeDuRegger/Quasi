<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $word = \App\Models\Word::select('name', 'definition', 'frequency')
        ->whereRaw('LENGTH(name) > ?', [5]) // Name longer than 5 characters
        ->where('frequency', '>', 0)       // Frequency greater than 0
        ->where('frequency', '<', 0.1)
        ->inRandomOrder()                  // Randomize the order of results
        ->first();                         // Fetch only one random word

    // Parse definitions
    $parsedDefinitions = [];
    if ($word && $word->definition) {
        // Split definitions by ';'
        $definitions = explode(';', $word->definition);

        foreach ($definitions as $def) {
            // Match the part of speech (e.g., 'n', 'adj') and parenthetical description
            preg_match('/^(\w+ \([^)]*\))\s(.+)/', trim($def), $matches);

            if ($matches) {
                $parsedDefinitions[] = [
                    'small' => $matches[1],  // Part of speech and parenthetical description
                    'definition' => $matches[2],  // Main definition
                ];
            } else {
                // If no match, treat the entire definition as plain text
                $parsedDefinitions[] = [
                    'small' => null,
                    'definition' => trim($def),
                ];
            }
        }
    }

    return view('welcome', compact('word', 'parsedDefinitions'));
});



