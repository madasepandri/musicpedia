<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Koleksi Saya') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Lagu yang Sudah Dibeli</h3>
                    @if ($purchases->isEmpty())
                        <p>Anda belum membeli lagu apapun. <a href="{{ route('songs.index') }}" class="text-blue-500 hover:underline">Lihat daftar lagu</a>.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach ($purchases as $purchase)
                                @if ($purchase->song)
                                    <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg shadow">
                                        <h4 class="text-xl font-bold">{{ $purchase->song->title }}</h4>
                                        <p class="text-gray-600 dark:text-gray-300">Artis: {{ $purchase->song->artist }}</p>
                                        <p class="text-gray-600 dark:text-gray-300">Album: {{ $purchase->song->album ?? '-' }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Dibeli pada: {{ $purchase->purchase_date->format('d M Y') }}</p>
                                        
                                        <div class="mt-4">
                                            <h5 class="font-medium">Putar Lagu (Versi Penuh):</h5>
                                            @if ($purchase->song->full_path)
                                                <audio controls class="w-full mt-2">
                                                    <source src="{{ asset('storage/' . $purchase->song->full_path) }}" type="audio/mpeg">
                                                    Browser Anda tidak mendukung elemen audio.
                                                </audio>
                                            @else
                                                <p>File lagu tidak tersedia.</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>