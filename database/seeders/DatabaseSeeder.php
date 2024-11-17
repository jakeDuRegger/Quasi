<?php

namespace Database\Seeders;

use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Word;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run the spider...
        // Collects ~ 17,000 obscure words

        // Supplement part of speech from lexicon csv.
        $words = Word::pluck('name')->toArray(); // Flat array of word names
        // Path to the CSV file
        $filePath = database_path('seeders/words_pos.csv');
        // Import part of speech for words.
//        $this->importCsvToSqlite($filePath, $words);

        // Fetch additional data from Datamuse API.
        $this->fetchDatamuseData();
    }

    /**
     * Import a limited number of records from CSV into SQLite database.
     */
    private function importCsvToSqlite(string $filePath, array $existingWords): void
    {
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
                $this->command->line('Updating ' . $word . ' part of speech to ' . $partOfSpeech );
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
                        'md' => 'd,s,f',
                    ]);

                    if ($response->ok()) {
                        $data = $response->json();
                        if (!empty($data)) {
                            $firstResult = $data[0];

                            // Extract definition, syllables, part of speech, and frequency
                            $definition = isset($firstResult['defs']) ? implode('; ', $firstResult['defs']) : null;
                            $syllables = $firstResult['numSyllables'] ?? null;
                            $frequency = null;

                            if (isset($firstResult['tags'])) {
                                foreach ($firstResult['tags'] as $tag) {
                                    if (str_starts_with($tag, 'f:')) {
                                        $frequency = (float)substr($tag, 2);
                                        break;
                                    }
                                }
                            }

                            // Update the word record in the database
                            Word::where('id', $word->id)->update([
                                'definition' => $definition,
                                'syllables' => $syllables,
                                'frequency' => $frequency,
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
}
