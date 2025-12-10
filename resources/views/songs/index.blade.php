<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Daftar Lagu') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Semua Lagu</h3>
                    @if ($songs->isEmpty())
                        <p>Tidak ada lagu yang tersedia saat ini.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach ($songs as $song)
                                <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg shadow">
                                    <h4 class="text-xl font-bold">{{ $song->title }}</h4>
                                    <p class="text-gray-600 dark:text-gray-300">Artis: {{ $song->artist }}</p>
                                    <p class="text-gray-600 dark:text-gray-300">Album: {{ $song->album ?? '-' }}</p>
                                    <p class="text-gray-600 dark:text-gray-300">Genre: {{ $song->genre ?? '-' }}</p>
                                    <p class="text-lg font-semibold mt-2">Harga: Rp {{ number_format($song->price, 2, ',', '.') }}</p>
                                    
                                    <div class="mt-4">
                                        <h5 class="font-medium">Trial Lagu:</h5>
                                        @if ($song->trial_path)
                                            <audio controls class="w-full mt-2">
                                                <source src="{{ asset('storage/' . $song->trial_path) }}" type="audio/mpeg">
                                                Browser Anda tidak mendukung elemen audio.
                                            </audio>
                                        @else
                                            <p>Trial tidak tersedia.</p>
                                        @endif
                                    </div>
                                    
                                    <div class="mt-4">
                                        @auth
                                            @if ($purchasedSongIds->contains($song->id))
                                                <p class="text-green-600 dark:text-green-400 font-semibold">Sudah Dibeli</p>
                                                <!-- Optionally, add a link to play the full song here later -->
                                            @else
                                                <form method="POST" action="{{ route('songs.purchase', $song) }}">
                                                    @csrf
                                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                                        Beli Lagu
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Login untuk membeli lagu.</p>
                                        @endauth
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>