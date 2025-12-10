<?php

namespace App\Http\Controllers;

use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Add this line

class SongController extends Controller
{
    /**
     * Display a listing of the songs.
     */
    public function index()
    {
        $songs = Song::all();
        $purchasedSongIds = collect(); // Default empty collection

        if (Auth::check()) {
            $user = Auth::user();
            $purchasedSongIds = $user->purchases->pluck('song_id'); // Assuming User model has 'purchases' relationship
        }
        
        return view('songs.index', compact('songs', 'purchasedSongIds'));
    }
}
