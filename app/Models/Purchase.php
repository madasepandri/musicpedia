<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // Import User model
use App\Models\Song; // Import Song model

class Purchase extends Model
{
    use HasFactory; // Often included with new models

    protected $fillable = [
        'user_id',
        'song_id',
        'purchase_price',
        'purchase_date',
    ];

    protected $casts = [
        'purchase_date' => 'datetime',
    ];

    /**
     * Get the user that owns the purchase.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the song that was purchased.
     */
    public function song()
    {
        return $this->belongsTo(Song::class);
    }
}
