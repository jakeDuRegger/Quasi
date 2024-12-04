<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Vote;
use App\Models\Word;
use Illuminate\Support\Str;

class WordController extends Controller
{
    // TODO
    // 1. Add a way to track progress of the user's (without a login) 'learning'.
    // 2. Have achievements...
    // 3. Track a favorites list.
    // 4. Users may vote on a word if they have ever heard of it before.

    public function show()
    {
        $guestId = request()->cookie('guest_id', Str::uuid()); // Generate a guest ID if not set

        list($word, $parsedDefinitions) = $this->getWord();

        // Check if the user has already voted on this word
        $existingVote = Vote::where('guest_id', $guestId)
            ->where('word_id', $word->id)
            ->first();

        if ($existingVote)
        {
            $seen = $existingVote->vote;
        }

        $categories = $this->getCategories($word);
        $favorited = $word->isFavorited();
        $favorites = $this->getFavorites();

        $word->toArray(); // remove laravel collection nonsense

        if (request()->ajax())
        {
            return response()->json([
                'word' => $word,
                'parsedDefinitions' => $parsedDefinitions,
                'categories' => $categories,
                'favorites' => $favorites,
                'favorited' => $favorited,
                'vote' => $seen ?? null
            ]);
        }

        return inertia('Word', [
            'word' => (object) $word,
            'parsedDefinitions' => $parsedDefinitions,
            'categories' => $categories,
            'favorites' => $favorites,
            'favorited' => $favorited,
            'vote' => $seen ?? null
        ]);
    }

    public function vote($wordId, bool $vote)
    {
        $guestId = request()->cookie('guest_id', Str::uuid()); // Generate a guest ID if not set

        $word = Word::findOrFail($wordId); //todo make this request more efficient

        $vote = $vote ? 1 : -1;

        // Check if the user has already voted on this word
        $existingVote = Vote::where('guest_id', $guestId)
            ->where('word_id', $word->id)
            ->first();

        if ($existingVote) {
            // If the user tries to vote again in the same way, do nothing
            if ($existingVote->vote === $vote) {
                $existingVote->delete();

                // Recalculate the vote count for the word
                return response()->json(['vote_count' => $word->vote_count]);
            }

            // Otherwise, change the vote
            $existingVote->vote = $vote;
            $existingVote->save();

        } else {
            // If the user hasn't voted yet, create a new vote
            Vote::create([
                'guest_id' => $guestId,
                'word_id' => $word->id,
                'vote' => $vote,
            ]);
        }
        return response()->json(['vote_count' => $word->vote_count]);
    }

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
        $word = Word::
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

    /**
     * @param mixed $word
     * @return array[]
     */
    private function getCategories(Word $word): array
    {
        $categories = [
            'Sounds similar to' => [
                'data' => json_decode($word->sounds_like, true),
                'example' => '"bardolatry", "bartoletti"',
            ],
            'Synonyms' => [
                'data' => json_decode($word->synonyms, true),
                'example' => '"worship", "devotion"',
            ],
            'Antonyms' => [
                'data' => json_decode($word->antonyms, true),
                'example' => '"disregard", "criticism"',
            ],
            'Homophones' => [
                'data' => json_decode($word->homophones, true),
                'example' => '"bard", "barred"',
            ],
            'Kind of like' => [
                'data' => json_decode($word->kind_of, true),
                'example' => '"artistic devotion", "theater practices"',
            ],
            'Part of' => [
                'data' => json_decode($word->part_of, true),
                'example' => '"bardolatry as part of Shakespearean studies"',
            ],
            'Associated words' => [
                'data' => json_decode($word->triggers, true),
                'example' => '"Shakespeare", "drama"',
            ],
            'Spelled similar to' => [
                'data' => json_decode($word->spelled_like, true),
                'example' => '"bardolatry", "bardology"',
            ],
            'More general' => [
                'data' => json_decode($word->more_general, true),
                'example' => '"literature", "performance arts"',
            ],
        ];
        return $categories;
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
