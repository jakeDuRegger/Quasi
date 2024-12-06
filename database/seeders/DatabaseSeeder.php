<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Word;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Collects ~ 17,000 obscure words
        if (Word::count() === 0)
        {
            // Run the spider...
            $this->command->info('Starting PhrontisterySpider to fetch words...');
            Artisan::call('roach:run PhrontisterySpider');
            $this->command->info('PhrontisterySpider completed. Data has been seeded.');
        }
        else
        {
            $this->command->info('Words already exist in the database. Skipping PhrontisterySpider.');
        }

        if (Word::whereNotNull('etymology')->count() === 0)
        {
            $this->command->info('Starting WikiSpider to fetch etymology data...');
            Artisan::call('roach:run WikiSpider');
            $this->command->info('WikiSpider completed. Etymology data has been added to words.');
        }
        else
        {
            $this->command->info('Etymologies already exist in the database. Skipping WikiSpider.');
        }


        // Supplement part of speech from lexicon csv.
        $words = Word::whereNull('part_of_speech')->pluck('name')->toArray(); // Flat array of word names
        // Path to the CSV file
        $filePath = database_path('seeders/words_pos.csv');
        // Import part of speech for words.
        $this->importCsvToSqlite($filePath, $words);

        // Fetch additional data from Datamuse API.
        $this->fetchDatamuseData();
    }

    /**
     * Import a limited number of records from CSV into SQLite database.
     */
    private function importCsvToSqlite(string $filePath, array $existingWords): void
    {
        $this->command->line("Loading part of speech data...");

        if (($handle = fopen($filePath, 'r')) !== false) {
            // Skip the header row
            fgetcsv($handle);

            while (($row = fgetcsv($handle)) !== false) {
                $word = trim($row[1]);
                $partOfSpeech = trim($row[2]);

                // Skip words not in the existing words array
                if (!in_array($word, $existingWords)) {
                    continue;
                }

                // Update part_of_speech in the database
                Word::where('name', $word)->update(['part_of_speech' => $partOfSpeech]);
                //$this->command->line('Updating ' . $word . ' part of speech to ' . $partOfSpeech);
            }

            fclose($handle);
        }

        $this->command->info("Part of speech data successfully imported.");
    }


    /**
     * Fetch additional data from Datamuse API for a limited number of records.
     */
    private function fetchDatamuseData(): void
    {
        // 11/20/2024
        // Found a bug from datamuse where it attempts to get a definition from a referenced term and it fails providing a template.
        // [{"word":"achatour","score":18,"defs":["n\t(historical) A purveyor of provisions; a provedore. [:Template:SAFESUBST:–Template:SAFESUBST: c.] "]}]
        $words = Word::all();

        foreach ($words as $index => $word) {
            $retryCount = 0;
            $maxRetries = 5;
            $delay = 1; // Start with 1 second

            while ($retryCount < $maxRetries) {
                try {
                    $index++;
                    // Print to the console when starting a request
                    $this->command->line("Processing word #{$index}: '{$word->name}' (ID: {$word->id})");

                    // Increase timeout to 30 seconds
                    $response = Http::timeout(30)->get('https://api.datamuse.com/words', [
                        'sp' => $word->name,
                        'md' => 'd,s,f,r',
                        'ipa' => 1,
                    ]);

                    if ($response->ok()) {
                        $data = $response->json();
                        if (!empty($data)) {
                            $firstResult = $data[0];

                            // Extract definition, syllables, part of speech, and frequency
                            $definition = isset($firstResult['defs']) ? implode('; ', $firstResult['defs']) : null;
                            $syllables = $firstResult['numSyllables'] ?? null;
                            $frequency = null;
                            $ipa_pronunciation = null;
                            $pronunciation = null;

                            if (isset($firstResult['tags'])) {
                                foreach ($firstResult['tags'] as $tag) {
                                    if (str_starts_with($tag, 'f:')) {
                                        $frequency = (float)substr($tag, 2);
                                    } else if (str_starts_with($tag, 'pron:')) {
                                        $pronunciation = substr($tag, 5);
                                    } else if (str_starts_with($tag, 'ipa_pron:')) {
                                        $ipa_pronunciation = substr($tag, 9);
                                    }
                                }
                            }

                            $relatedData = [
                                'related_meanings' => $this->fetchRelatedWords($word->name, 'ml'),
                                'sounds_like' => $this->fetchRelatedWords($word->name, 'sl'),
                                'spelled_like' => $this->fetchRelatedWords($word->name, 'sp'),
                                'synonyms' => $this->fetchRelatedWords($word->name, 'rel_syn'),
                                'antonyms' => $this->fetchRelatedWords($word->name, 'rel_ant'),
                                'triggers' => $this->fetchRelatedWords($word->name, 'rel_trg'),
                                'homophones' => $this->fetchRelatedWords($word->name, 'rel_hom'),
                                'kind_of' => $this->fetchRelatedWords($word->name, 'rel_spc'),
                                'more_general' => $this->fetchRelatedWords($word->name, 'rel_gen'),
                                'part_of' => $this->fetchRelatedWords($word->name, 'rel_par'),
                            ];

                            // Update the word record in the database
                            Word::where('id', $word->id)->update([
                                'definition' => $definition,
                                'syllables' => $syllables,
                                'frequency' => $frequency,
                                'pronunciation' => $pronunciation,
                                'ipa_pronunciation' => $ipa_pronunciation,

                                'related_meanings' => $relatedData['related_meanings'],
                                'sounds_like' => $relatedData['sounds_like'],
                                'spelled_like' => $relatedData['spelled_like'],
                                'synonyms' => $relatedData['synonyms'],
                                'antonyms' => $relatedData['antonyms'],
                                'triggers' => $relatedData['triggers'],
                                'homophones' => $relatedData['homophones'],
                                'kind_of' => $relatedData['kind_of'],
                                'more_general' => $relatedData['more_general'],
                                'part_of' => $relatedData['part_of'],
                            ]);
                        }

                        // Print success to the console
                        $this->command->info("Successfully processed '{$word->name}' (ID: {$word->id})");
                        break;
                    } else {
                        throw new \Exception('Received non-OK response');
                    }
                } catch (\Exception $e) {
                    $retryCount++;
                    if ($retryCount == $maxRetries) {
                        // Print error to the console
                        $this->command->error("Failed to process '{$word->name}' (ID: {$word->id}) after {$maxRetries} retries: {$e->getMessage()}");
                        break;
                    }

                    // Wait before retrying
                    sleep($delay);
                    $delay *= 2; // Exponential backoff
                }
            }
            usleep(10000); // 100ms
        }
    }

    private function fetchRelatedWords(string $word, string $relType): ?array
    {
        try {
            $response = Http::timeout(30)->get('https://api.datamuse.com/words', [
                $relType => $word,
            ]);

            if ($response->ok()) {
                $data = $response->json();
                return !empty($data) ? array_column($data, 'word') : [];
            }

            return [];
        } catch (\Exception $e) {
            $this->command->error("Error fetching '{$relType}' for '{$word}': {$e->getMessage()}");
            return [];
        }
    }

    // todo could fetch images... https://en.wiktionary.org/api/rest_v1/page/media-list/aardwolf
    private function fetchEtymology()
    {
        // todo... https://en.wiktionary.org/api/rest_v1/#/Page%20content
        try {
            // Fetch the HTML content from the Wiktionary API
            $response = Http::get("https://en.wiktionary.org/api/rest_v1/page/html/{$word}");

            if ($response->ok()) {
                $htmlContent = $response->body();

                // Load the HTML into DOMDocument
                $dom = new \DOMDocument();
                @$dom->loadHTML($htmlContent); // Suppress warnings for malformed HTML

                // Use XPath to find the "Etymology" section
                $xpath = new \DOMXPath($dom);
                $etymologyNodes = $xpath->query("//h2[.='Etymology']/following-sibling::*[not(self::h2)][1]");

                if ($etymologyNodes->length > 0) {
                    // Extract and return the etymology text
                    return $dom->saveHTML($etymologyNodes->item(0));
                }
            }
        } catch (\Exception $e) {
            echo "Error fetching etymology for {$word}: " . $e->getMessage();
        }

        return null; // Return null if etymology not found
    }
}
