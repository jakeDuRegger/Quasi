<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Path to the CSV file
        $filePath = database_path('seeders/words_pos.csv');

        // Import a limited number of records from the CSV
        $this->importCsvToSqlite($filePath, 5000);

        // Fetch additional data from Datamuse API for only 5,000 records
        $this->fetchDatamuseData(5000);
    }

    /**
     * Import a limited number of records from CSV into SQLite database.
     */
    private function importCsvToSqlite(string $filePath, int $limit): void
    {
        // Enable SQLite extensions for CSV import
        DB::unprepared("PRAGMA foreign_keys = OFF;");
        DB::unprepared("PRAGMA synchronous = OFF;");

        // Read and process the CSV file
        if (($handle = fopen($filePath, 'r')) !== false) {
            // Skip the header row
            fgetcsv($handle);

            $count = 0;

            while (($row = fgetcsv($handle)) !== false && $count < $limit) {
                DB::table('words')->insert([
                    'name' => trim($row[1]),     // 'word'
                    'part_of_speech' => trim($row[2]), // 'pos_tag'
                ]);
                $count++;
            }

            fclose($handle);
        }
    }

    /**
     * Fetch additional data from Datamuse API for a limited number of records.
     */
    private function fetchDatamuseData(int $limit): void
    {
        // Get the first 5,000 records from the database
        $words = DB::table('words')->take($limit)->get();

        foreach ($words as $word) {
            // Fetch data from Datamuse API
            $response = Http::get('https://api.datamuse.com/words', [
                'sp' => $word->name,
                'md' => 'd,s,f',
            ]);

            $data = $response->json();
            if (!empty($data)) {
                $firstResult = $data[0];

                // Extract definition, syllables, and frequency
                $definition = isset($firstResult['defs']) ? implode('; ', $firstResult['defs']) : null;
                $syllables = $firstResult['numSyllables'] ?? null;
                $frequency = null;

                if (isset($firstResult['tags'])) {
                    foreach ($firstResult['tags'] as $tag) {
                        if (str_starts_with($tag, 'f:')) {
                            $frequency = (float) substr($tag, 2);
                            break;
                        }
                    }
                }

                // Update the word record in the database
                DB::table('words')->where('id', $word->id)->update([
                    'definition' => $definition,
                    'syllables' => $syllables,
                    'frequency' => $frequency,
                ]);
            }
        }
    }
}
