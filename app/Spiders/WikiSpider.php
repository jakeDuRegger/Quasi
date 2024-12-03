<?php

namespace App\Spiders;

use App\Models\Word;
use App\Processors\WikiSpiderProcessor;
use Generator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use RoachPHP\Downloader\Middleware\RequestDeduplicationMiddleware;
use RoachPHP\Extensions\LoggerExtension;
use RoachPHP\Extensions\StatsCollectorExtension;
use RoachPHP\Http\Request;
use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;
use RoachPHP\Spider\ParseResult;


class WikiSpider extends BasicSpider
{
    public array $startUrls = [
        // Auto-generated
    ];

    public array $downloaderMiddleware = [
        RequestDeduplicationMiddleware::class,
    ];

    public array $spiderMiddleware = [
        //
    ];

    public array $itemProcessors = [
        WikiSpiderProcessor::class
    ];

    public array $extensions = [
        LoggerExtension::class,
        StatsCollectorExtension::class,
    ];

    public int $concurrency = 2;

    public int $requestDelay = 1;

    /**
     * Dynamically generate the initial requests.
     *
     * @return Request[]
     */
    protected function initialRequests(): array
    {
        // Fetch words from the database
        $words = Word::pluck('name')->toArray();

        // Build requests dynamically
        return array_map(function ($word) {
            $url = "https://en.wiktionary.org/api/rest_v1/page/html/" . $word;
            return new Request('GET', $url, [$this, 'parse']);
        }, $words);
    }

    /**
     * @return Generator<ParseResult>
     */
    public function parse(Response $response): Generator
    {
        // todo check on the different etymology.... because some from Polish etc.

        // get the current word
        $word = basename($response->getRequest()->getUri());
        $etymology = null;
        // get the etymology
        try {
            // Check if the element exists before calling text()
            $etymologySections = $response->filter('h3[id^="Etymology"]');

            if ($etymologySections->count() > 0) {
                $etymologies = [];
                $etymologySections->each(function ($section) use (&$etymologies) {
                    // Get the first <p> sibling after each <h3>
                    $paragraph = $section->nextAll()->filter('p')->first();
                    if ($paragraph->count() > 0) {
                        $etymologies[] = $paragraph->text();
                    }
                });

                // Combine the etymologies into a single string or keep as an array
                $etymology = implode("\t", $etymologies);
            } else {
                Log::warning("Etymology section not found for word: $word");
            }
        } catch (\Exception $e) {
            Log::error("Exception for word $word: " . $e->getMessage());
        }

        $etymology = trim($etymology) !== '' ? $etymology : null;

        // Yield the parsed data
        yield ParseResult::item([
            'word' => $word,
            'etymology' => $etymology,
        ]);


    }
}
