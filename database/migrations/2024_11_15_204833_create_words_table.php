<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('words', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('name')->unique();
            $table->string('definition')->nullable();
            $table->enum('part_of_speech', [
                'CC',  // coordinating conjunction
                'CD',  // cardinal digit
                'DT',  // determiner
                'EX',  // existential there
                'FW',  // foreign word
                'IN',  // preposition/subordinating conjunction
                'JJ',  // adjective (large)
                'JJR', // adjective, comparative (larger)
                'JJS', // adjective, superlative (largest)
                'LS',  // list item marker
                'MD',  // modal (could, will)
                'NN',  // noun, singular
                'NNS', // noun plural
                'NNP', // proper noun, singular
                'NNPS', // proper noun, plural
                'PDT', // predeterminer
                'POS', // possessive ending (parent's)
                'PRP', // personal pronoun (hers, herself, him, himself)
                'PRP$', // possessive pronoun (her, his, mine, my, our)
                'RB',  // adverb (occasionally, swiftly)
                'RBR', // adverb, comparative (greater)
                'RBS', // adverb, superlative (biggest)
                'RP',  // particle (about)
                'SYM', // symbol
                'TO',  // infinite marker (to)
                'UH',  // interjection (goodbye)
                'VB',  // verb (ask)
                'VBG', // verb gerund (judging)
                'VBD', // verb past tense (pleaded)
                'VBN', // verb past participle (reunified)
                'VBP', // verb, present tense not 3rd person singular (wrap)
                'VBZ', // verb, present tense with 3rd person singular (bases)
                'WDT', // wh-determiner (that, what)
                'WP',  // wh-pronoun (who)
                'WP$', // possessive wh-pronoun
                'WRB'  // wh-adverb (how)
            ])->nullable();

            $table->string('language')->default('en');
            $table->float('frequency')->default(0);
            $table->integer('syllables')->nullable();
            $table->text('example_sentence')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('words');
    }
};
