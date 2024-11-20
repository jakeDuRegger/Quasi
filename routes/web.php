<?php

use Illuminate\Support\Facades\Route;

/*
 * TODO: Create a way to track progress of the user's (without a login) 'learning'.
 * TODO: Have achievements... lol
 * TODO: Track a favorites list.
 */



Route::get('/', function () {
    // transformation map
    $posMap = [
        'n' => 'noun',
        'v' => 'verb',
        'adj' => 'adjective',
        'adv' => 'adverb',
        'pron' => 'pronoun',
        'prep' => 'preposition',
        'conj' => 'conjunction',
        'interj' => 'interjection',
        'suf' => 'suffix',
        'pref' => 'prefix',
    ];

    start:
    $word = \App\Models\Word::select('name', 'definition', 'frequency')
        ->whereRaw('LENGTH(name) > ?', [5]) // Name longer than 5 characters
        ->where('frequency', '>', 0)
        ->where('frequency', '<', 0.01)
        ->inRandomOrder()                  // Randomize the order of results
        ->first();                         // Fetch only one random word

    // parse the brackets (i.e. a hyperlinked word to have a tooltip)

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
                    'pos_string' => $posMap[strtolower($matches[1])],  // Part of speech (e.g., 'n', 'v')
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

            // now look for referenced words

        }


    }

    if (empty($parsedDefinitions))
    {
        goto start;
    }

    return view('word', compact('word', 'parsedDefinitions'));
});



