<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Song; // Import the Song model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // For file uploads
use Illuminate\Validation\Rule;

class SongController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $songs = Song::all();
        return view('admin.songs.index', compact('songs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.songs.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'required|string|max:255',
            'album' => 'nullable|string|max:255',
            'genre' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'trial_file' => 'required|file|mimes:mp3,wav|max:10240', // Max 10MB
            'full_file' => 'nullable|file|mimes:mp3,wav|max:51200', // Max 50MB
        ]);

        $trialPath = $request->file('trial_file')->store('audio/trials', 'public');
        $fullPath = null;
        if ($request->hasFile('full_file')) {
            $fullPath = $request->file('full_file')->store('audio/full', 'public');
        }

        Song::create([
            'title' => $validatedData['title'],
            'artist' => $validatedData['artist'],
            'album' => $validatedData['album'],
            'genre' => $validatedData['genre'],
            'price' => $validatedData['price'],
            'trial_path' => $trialPath,
            'full_path' => $fullPath,
        ]);

        return redirect()->route('admin.songs.index')->with('success', 'Lagu berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Song $song)
    {
        return view('admin.songs.show', compact('song'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Song $song)
    {
        return view('admin.songs.edit', compact('song'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Song $song)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'artist' => 'required|string|max:255',
            'album' => 'nullable|string|max:255',
            'genre' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'trial_file' => 'nullable|file|mimes:mp3,wav|max:10240', // Max 10MB
            'full_file' => 'nullable|file|mimes:mp3,wav|max:51200', // Max 50MB
        ]);

        if ($request->hasFile('trial_file')) {
            // Delete old trial file
            if ($song->trial_path) {
                Storage::disk('public')->delete($song->trial_path);
            }
            $validatedData['trial_path'] = $request->file('trial_file')->store('audio/trials', 'public');
        }

        if ($request->hasFile('full_file')) {
            // Delete old full file
            if ($song->full_path) {
                Storage::disk('public')->delete($song->full_path);
            }
            $validatedData['full_path'] = $request->file('full_file')->store('audio/full', 'public');
        } else {
            // If full_file is explicitly nullified (e.g., checkbox 'remove full song')
            if ($request->has('remove_full_file') && $request->input('remove_full_file')) {
                if ($song->full_path) {
                    Storage::disk('public')->delete($song->full_path);
                }
                $validatedData['full_path'] = null;
            }
        }
        
        $song->update($validatedData);

        return redirect()->route('admin.songs.index')->with('success', 'Lagu berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Song $song)
    {
        // Delete associated files
        if ($song->trial_path) {
            Storage::disk('public')->delete($song->trial_path);
        }
        if ($song->full_path) {
            Storage::disk('public')->delete($song->full_path);
        }
        
        $song->delete();
        return redirect()->route('admin.songs.index')->with('success', 'Lagu berhasil dihapus.');
    }
}
