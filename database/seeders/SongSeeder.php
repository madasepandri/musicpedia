<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Song; // Import the Song model
use Illuminate\Support\Facades\Storage; // To create dummy audio files

class SongSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure the public storage directory exists
        if (!Storage::disk('public')->exists('audio')) {
            Storage::disk('public')->makeDirectory('audio');
        }

        // Create dummy audio files
        $dummyAudioContent = 'Dummy audio content for trial';
        Storage::disk('public')->put('audio/trial_song_1.mp3', $dummyAudioContent);
        Storage::disk('public')->put('audio/trial_song_2.mp3', $dummyAudioContent);
        Storage::disk('public')->put('audio/trial_song_3.mp3', $dummyAudioContent);
        Storage::disk('public')->put('audio/full_song_1.mp3', 'Full audio content for song 1');
        Storage::disk('public')->put('audio/full_song_2.mp3', 'Full audio content for song 2');

        Song::create([
            'title' => 'Lagu Cinta',
            'artist' => 'Band Pop',
            'album' => 'Album Terbaik',
            'genre' => 'Pop',
            'price' => 9.99,
            'trial_path' => 'audio/trial_song_1.mp3',
            'full_path' => 'audio/full_song_1.mp3',
        ]);

        Song::create([
            'title' => 'Melodi Rindu',
            'artist' => 'Penyanyi Solo',
            'album' => 'Harmoni Jiwa',
            'genre' => 'Akustik',
            'price' => 12.50,
            'trial_path' => 'audio/trial_song_2.mp3',
            'full_path' => 'audio/full_song_2.mp3',
        ]);

        Song::create([
            'title' => 'Semangat Pagi',
            'artist' => 'Grup Rock',
            'album' => 'Energi Positif',
            'genre' => 'Rock',
            'price' => 15.00,
            'trial_path' => 'audio/trial_song_3.mp3',
            'full_path' => null, // This song hasn't been "purchased" or full version uploaded yet
        ]);
    }
}
