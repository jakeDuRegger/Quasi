<?php

namespace App\Processors;

use App\Models\Word;
use Illuminate\Support\Facades\DB;
use RoachPHP\ItemPipeline\ItemInterface;
use RoachPHP\ItemPipeline\Processors\ItemProcessorInterface;
use RoachPHP\Support\Configurable;

class PhrontisterySpiderProcessor implements ItemProcessorInterface
{
    use Configurable;

    public function processItem(ItemInterface $item): ItemInterface
    {
        // Save the word and definition to the database
        Word::updateOrCreate(
            ['name' => $item->get('word')], // Unique constraint
            [
                'definition' => $item->get('definition'),
            ]
        );

        return $item;
    }

}
