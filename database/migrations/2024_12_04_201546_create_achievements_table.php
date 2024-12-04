<?php

use App\Models\AchievementReward;
use App\Models\AchievementRule;
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
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes(); // hide the achievement

            // Basic info
            $table->string('name')->unique();
            $table->text('description');
            // store the image of the achievement (todo learn some graphic design systems!)
            $table->string('image')->nullable();

            // status
            $table->boolean('secret')->default(false); // Hidden achievements

            // rules and rewards for each achievement
            $table->foreignIdFor(AchievementRule::class);
            $table->foreignIdFor(AchievementReward::class);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
