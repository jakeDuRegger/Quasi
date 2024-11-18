<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    start:
    $word = \App\Models\Word::select('name', 'definition', 'frequency')
        ->whereRaw('LENGTH(name) > ?', [5]) // Name longer than 5 characters
        ->where('frequency', '>', 0)
        ->where('frequency', '<', 0.1)
        ->inRandomOrder()                  // Randomize the order of results
        ->first();                         // Fetch only one random word

    // Parse definitions
    $parsedDefinitions = [];
    if ($word && $word->definition) {
        // Split definitions by ';'
        $definitions = explode(';', $word->definition);

        foreach ($definitions as $def) {
            // Match the format: pos\t(parenthesis) Definition or pos\tDefinition
            preg_match('/^(\w+)\t(?:\(([^)]+)\))?\s*(.+)/', trim($def), $matches);

            if ($matches) {
                $parsedDefinitions[] = [
                    'pos_string' => $matches[1],  // Part of speech (e.g., 'n', 'v')
                    'small' => strlen($matches[2]) != 0 ? '(' . $matches[2] . ')' : null,  // Parenthetical description, if present
                    'definition' => $matches[3],  // Main definition
                ];
            } else {
                // If no match, treat the entire definition as plain text
                $parsedDefinitions[] = [
                    'pos_string' => null,
                    'small' => null,
                    'definition' => trim($def),
                ];
            }
        }


    }

    if (empty($parsedDefinitions))
    {
        goto start;
    }

    return view('word', compact('word', 'parsedDefinitions'));
});



