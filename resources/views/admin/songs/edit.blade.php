<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Lagu') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Form Edit Lagu</h3>

                    <form method="POST" action="{{ route('admin.songs.update', $song) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Title -->
                        <div class="mb-4">
                            <x-input-label for="title" :value="__('Judul Lagu')" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $song->title)" required autofocus />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <!-- Artist -->
                        <div class="mb-4">
                            <x-input-label for="artist" :value="__('Artis')" />
                            <x-text-input id="artist" class="block mt-1 w-full" type="text" name="artist" :value="old('artist', $song->artist)" required />
                            <x-input-error :messages="$errors->get('artist')" class="mt-2" />
                        </div>

                        <!-- Album -->
                        <div class="mb-4">
                            <x-input-label for="album" :value="__('Album')" />
                            <x-text-input id="album" class="block mt-1 w-full" type="text" name="album" :value="old('album', $song->album)" />
                            <x-input-error :messages="$errors->get('album')" class="mt-2" />
                        </div>

                        <!-- Genre -->
                        <div class="mb-4">
                            <x-input-label for="genre" :value="__('Genre')" />
                            <x-text-input id="genre" class="block mt-1 w-full" type="text" name="genre" :value="old('genre', $song->genre)" />
                            <x-input-error :messages="$errors->get('genre')" class="mt-2" />
                        </div>

                        <!-- Price -->
                        <div class="mb-4">
                            <x-input-label for="price" :value="__('Harga')" />
                            <x-text-input id="price" class="block mt-1 w-full" type="number" step="0.01" name="price" :value="old('price', $song->price)" required />
                            <x-input-error :messages="$errors->get('price')" class="mt-2" />
                        </div>

                        <!-- Trial File -->
                        <div class="mb-4">
                            <x-input-label for="trial_file" :value="__('File Trial (MP3/WAV)')" />
                            @if ($song->trial_path)
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">File saat ini: <a href="{{ asset('storage/' . $song->trial_path) }}" target="_blank" class="text-blue-500 hover:underline">Lihat</a></p>
                            @endif
                            <input id="trial_file" class="block mt-1 w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-700 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 focus:outline-none" type="file" name="trial_file" />
                            <x-input-error :messages="$errors->get('trial_file')" class="mt-2" />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Kosongkan jika tidak ingin mengubah file.</p>
                        </div>

                        <!-- Full File -->
                        <div class="mb-4">
                            <x-input-label for="full_file" :value="__('File Penuh (MP3/WAV) - Opsional')" />
                            @if ($song->full_path)
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">File saat ini: <a href="{{ asset('storage/' . $song->full_path) }}" target="_blank" class="text-blue-500 hover:underline">Lihat</a></p>
                                <div class="flex items-center mb-2">
                                    <input type="checkbox" name="remove_full_file" id="remove_full_file" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800">
                                    <x-input-label for="remove_full_file" :value="__('Hapus File Penuh')" class="ml-2" />
                                </div>
                            @endif
                            <input id="full_file" class="block mt-1 w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-700 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 focus:outline-none" type="file" name="full_file" />
                            <x-input-error :messages="$errors->get('full_file')" class="mt-2" />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Kosongkan jika tidak ingin mengubah file.</p>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Perbarui Lagu') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>