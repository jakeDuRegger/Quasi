<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WordController extends Controller
{
    // get and remove referenced words
    private function referenceWords(&$parsedDefinitions)
    {
        // edit the parsedDefinitions in place
        $newReferences = [];

        // look for S\wS[\w+]
        // that becomes
        // S\wS -> referencedWord
        // [\w+] -> referencedDefinition
        foreach ($parsedDefinitions as &$definition) {
            $word = null;
            $def = null;
            // Match patterns like S\wS[\w+]
            if (preg_match_all('/(\S+)\s*\[([^]]+)]/', $definition['definition'], $matches)) {
                $word = $matches[1][0] ?? null;
                $def = $matches[2][0] ?? null;

                // Remove the referenced word and definition from the main string
                $definition['definition'] = trim(str_replace($matches[0], '', $definition['definition']));
            }
            $definition['referencedWord'] = $word;
            $definition['referencedDefinition'] = $def;
        }
    }

    // return a word
    private function getWord(): array
    {
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
        $word = \App\Models\Word::
            where('name', '!=', 'acatour') // bug with acatour... (actually looks like a lot of template errors...
            ->whereRaw('LENGTH(name) > ?', [3]) // Name longer than 5 characters
            ->where('frequency', '>', 0)
            ->where('frequency', '<', 0.01)
            ->whereNotNull('pronunciation')
//            ->whereRaw("definition like '%\[%' escape '\'")
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
            }
        }

        if (empty($parsedDefinitions)) {
            goto start;
        }

        // now look for referenced words
        $this->referenceWords($parsedDefinitions);
        return array($word, $parsedDefinitions, $matches);
    }

    public function show()
    {
        list($word, $parsedDefinitions) = $this->getWord();

        return view('word', [
            'word' => $word,
            'parsedDefinitions' => $parsedDefinitions,
        ]);
    }

    public function apiShow()
    {
        list($word, $parsedDefinitions) = $this->getWord();

        return response()->json([
            'word' => $word,
            'parsedDefinitions' => $parsedDefinitions
        ]);
    }

}
