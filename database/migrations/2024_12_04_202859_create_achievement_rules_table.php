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
        Schema::create('achievement_rules', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // Rule definitions
            $table->string('type'); // Type of rule (e.g., 'voting', 'favorites', 'category')
            $table->string('key'); // Specific event to track (e.g., 'votes_cast', 'favorites_added')
            $table->integer('threshold')->nullable(); // Minimum value to unlock (e.g., 50 votes)
            $table->json('conditions')->nullable(); // Advanced conditions (e.g., {"time": "night", "category": "rare_words"})

            // For multi-step achievements
            $table->boolean('is_progressive')->default(false); // Whether it's a progressive rule
            $table->integer('progress_steps')->nullable(); // Number of steps for progressive achievements
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievement_rules');
    }
};
