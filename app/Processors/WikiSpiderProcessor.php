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
        $etymology = $item->get('etymology');
        $wordName = $item->get('word');

        if (!is_null($etymology)) {
            // Update the word in the database only if etymology is not null
            Word::where('name', '=', $wordName)
                ->update(['etymology' => $etymology]);
        }

        return $item;
    }

}
