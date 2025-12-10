<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Purchase; // Import Purchase model

class Song extends Model
{
    protected $fillable = [
        'title',
        'artist',
        'album',
        'genre',
        'price',
        'trial_path',
        'full_path',
    ];

    /**
     * Get the purchases for the song.
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
