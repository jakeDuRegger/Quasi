<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Word extends Model
{
    protected $guarded = [];

    protected $appends = [
        'vote_count',
    ];

    protected function voteCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->votes()->sum('vote'),
        );
    }

    public function isFavorited(): bool
    {
        $guestId = request()->cookie('guest_id'); // check if guest id
        if ($guestId)
        {
            // check if word is favorited
            return Favorite::where('word_id', $this->id)->exists();
        }
        return false;
    }

    public function votes(): HasMany
    {
        // Get the word's votes from all users
        return $this->hasMany(Vote::class);
    }
}
