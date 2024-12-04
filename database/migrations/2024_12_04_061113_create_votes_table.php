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
        Schema::create('votes', function (Blueprint $table) {
            $table->id();

            $table->uuid('guest_id');
            $table->foreignIdFor(\App\Models\Word::class)->constrained()->cascadeOnDelete();

            $table->integer('vote');
            $table->unique(['guest_id', 'word_id']); // ensure only one vote per user

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
