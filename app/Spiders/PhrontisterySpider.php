<?php

namespace App\Spiders;

use App\Processors\PhrontisterySpiderProcessor;
use Generator;
use RoachPHP\Downloader\Middleware\RequestDeduplicationMiddleware;
use RoachPHP\Extensions\LoggerExtension;
use RoachPHP\Extensions\StatsCollectorExtension;
use RoachPHP\Http\Response;
use RoachPHP\Spider\BasicSpider;
use RoachPHP\Spider\ParseResult;

class PhrontisterySpider extends BasicSpider
{
    public array $startUrls = [
        'https://phrontistery.info/a.html',
        'https://phrontistery.info/b.html',
        'https://phrontistery.info/c.html',
        'https://phrontistery.info/d.html',
        'https://phrontistery.info/e.html',
        'https://phrontistery.info/f.html',
        'https://phrontistery.info/g.html',
        'https://phrontistery.info/h.html',
        'https://phrontistery.info/i.html',
        'https://phrontistery.info/j.html',
        'https://phrontistery.info/k.html',
        'https://phrontistery.info/l.html',
        'https://phrontistery.info/m.html',
        'https://phrontistery.info/n.html',
        'https://phrontistery.info/o.html',
        'https://phrontistery.info/p.html',
        'https://phrontistery.info/q.html',
        'https://phrontistery.info/u.html',
        'https://phrontistery.info/r.html',
        'https://phrontistery.info/s.html',
        'https://phrontistery.info/t.html',
        'https://phrontistery.info/u.html',
        'https://phrontistery.info/x.html',
        'https://phrontistery.info/y.html',
        'https://phrontistery.info/z.html',
    ];
    public array $downloaderMiddleware = [
        RequestDeduplicationMiddleware::class,
    ];

    public array $spiderMiddleware = [
        //
    ];

    public array $itemProcessors = [
        PhrontisterySpiderProcessor::class,
    ];

    public array $extensions = [
        LoggerExtension::class,
        StatsCollectorExtension::class,
    ];

    public int $concurrency = 2;

    public int $requestDelay = 1;

    /**
     * @return Generator<ParseResult>
     */
    public function parse(Response $response): Generator
    {
        // table class="words" words are on a table the left td and definition on the right td
        $rows = $response->filter('table.words tr')->slice(1)->each(function ($node) {
            $columns = $node->filter('td');
           if ($columns->count() == 2)
           {
               $word = $columns->eq(0)->text();
               $definition = $columns->eq(1)->text();

               return [
                   'word' => trim($word),
                   'definition' => trim($definition),
               ];
           }
            return null;
        });

        foreach ($rows as $item) {
            if ($item)
            {
                yield ParseResult::item($item);
            }
        }

    }
}
