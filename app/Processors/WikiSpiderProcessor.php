<?php

namespace App\Processors;

use App\Models\Word;
use Illuminate\Support\Facades\DB;
use RoachPHP\ItemPipeline\ItemInterface;
use RoachPHP\ItemPipeline\Processors\ItemProcessorInterface;
use RoachPHP\Support\Configurable;

class WikiSpiderProcessor implements ItemProcessorInterface
{
    use Configurable;

    public function processItem(ItemInterface $item): ItemInterface
    {
        // Save the word and definition to the database
        Word::where('name', '=', $item->get('word')
        )->update(['etymology' => $item->get('etymology')],);

        return $item;
    }

}
