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
        Schema::create('achievement_rewards', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->integer('points')->default(0); // Points or XP rewarded
            $table->json('custom_rewards')->nullable(); // For additional reward types (e.g., {"item": "gold_badge"})

            $table->boolean('badge')->default(false); // whether it has badge
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievement_rewards');
    }
};
