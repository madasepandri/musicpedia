<?php

namespace App\Http\Controllers;

use App\Models\Song;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    /**
     * Display the user's collection of purchased songs.
     */
    public function index()
    {
        $user = Auth::user();
        $purchases = $user->purchases()->with('song')->get();

        return view('collection.index', compact('purchases'));
    }

    /**
     * Handle the purchase of a song.
     */
    public function store(Request $request, Song $song)
    {
        // Ensure user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Anda harus login untuk membeli lagu.');
        }

        $user = Auth::user();

        // Check if the user has already purchased this song
        if (Purchase::where('user_id', $user->id)->where('song_id', $song->id)->exists()) {
            return back()->with('error', 'Anda sudah memiliki lagu ini.');
        }

        // For simplicity, we are not implementing actual payment gateway
        // In a real application, this would involve a payment process

        Purchase::create([
            'user_id' => $user->id,
            'song_id' => $song->id,
            'purchase_price' => $song->price,
            'purchase_date' => now(),
        ]);

        return redirect()->route('songs.index')->with('success', 'Lagu berhasil dibeli!');
    }
}
