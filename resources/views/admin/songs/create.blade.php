<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Tambah Lagu Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Form Tambah Lagu</h3>

                    <form method="POST" action="{{ route('admin.songs.store') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Title -->
                        <div class="mb-4">
                            <x-input-label for="title" :value="__('Judul Lagu')" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title')" required autofocus />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <!-- Artist -->
                        <div class="mb-4">
                            <x-input-label for="artist" :value="__('Artis')" />
                            <x-text-input id="artist" class="block mt-1 w-full" type="text" name="artist" :value="old('artist')" required />
                            <x-input-error :messages="$errors->get('artist')" class="mt-2" />
                        </div>

                        <!-- Album -->
                        <div class="mb-4">
                            <x-input-label for="album" :value="__('Album')" />
                            <x-text-input id="album" class="block mt-1 w-full" type="text" name="album" :value="old('album')" />
                            <x-input-error :messages="$errors->get('album')" class="mt-2" />
                        </div>

                        <!-- Genre -->
                        <div class="mb-4">
                            <x-input-label for="genre" :value="__('Genre')" />
                            <x-text-input id="genre" class="block mt-1 w-full" type="text" name="genre" :value="old('genre')" />
                            <x-input-error :messages="$errors->get('genre')" class="mt-2" />
                        </div>

                        <!-- Price -->
                        <div class="mb-4">
                            <x-input-label for="price" :value="__('Harga')" />
                            <x-text-input id="price" class="block mt-1 w-full" type="number" step="0.01" name="price" :value="old('price')" required />
                            <x-input-error :messages="$errors->get('price')" class="mt-2" />
                        </div>

                        <!-- Trial File -->
                        <div class="mb-4">
                            <x-input-label for="trial_file" :value="__('File Trial (MP3/WAV)')" />
                            <input id="trial_file" class="block mt-1 w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-700 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 focus:outline-none" type="file" name="trial_file" required />
                            <x-input-error :messages="$errors->get('trial_file')" class="mt-2" />
                        </div>

                        <!-- Full File -->
                        <div class="mb-4">
                            <x-input-label for="full_file" :value="__('File Penuh (MP3/WAV) - Opsional')" />
                            <input id="full_file" class="block mt-1 w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-700 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 focus:outline-none" type="file" name="full_file" />
                            <x-input-error :messages="$errors->get('full_file')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Simpan Lagu') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>