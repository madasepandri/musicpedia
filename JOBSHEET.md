# Jobsheet Praktikum: Membangun Aplikasi Web "Geminimusicpedia" dengan Laravel 12

**Tujuan Pembelajaran:**

Setelah menyelesaikan praktikum ini, mahasiswa diharapkan mampu:
1.  Menginisialisasi dan mengkonfigurasi proyek baru menggunakan Laravel.
2.  Menerapkan sistem autentikasi bawaan Laravel Breeze.
3.  Merancang dan membuat skema database menggunakan Migrations.
4.  Mengelola data (CRUD - Create, Read, Update, Delete) menggunakan Eloquent ORM dan Controller.
5.  Mengimplementasikan hak akses pengguna (Roles & Permissions) dengan Middleware dan Gates.
6.  Membuat tampilan (views) dengan Blade templating engine dan Tailwind CSS.
7.  Mengelola file upload untuk konten seperti gambar atau audio.
8.  Membuat fungsionalitas ekspor data sederhana (misalnya, ke format CSV).

**Alat dan Bahan:**
*   Web Server (Laragon, XAMPP, atau sejenisnya) dengan PHP 8.2+.
*   Composer (Dependency Manager for PHP).
*   Node.js dan NPM.
*   Web Browser (Chrome, Firefox, dll).
*   Teks Editor atau IDE (Visual Studio Code, PhpStorm, dll).

---

## Langkah-Langkah Praktikum

### **1. Inisialisasi Proyek Laravel dan Breeze**

Buat proyek Laravel baru.
```bash
composer create-project laravel/laravel geminimusicpedia
cd geminimusicpedia
```

Instal Laravel Breeze untuk sistem autentikasi.
```bash
composer require laravel/breeze
php artisan breeze:install blade
npm install
npm run dev
```

Jalankan migrasi awal untuk membuat tabel standar Laravel.
```bash
php artisan migrate
```
Pastikan `DB_CONNECTION=sqlite` di file `.env`.

### **2. Desain dan Implementasi Skema Database**

Membuat migrasi untuk tabel `songs`.
```bash
php artisan make:model Song -m
```

Isi file migrasi `database/migrations/*_create_songs_table.php` dengan kolom-kolom berikut:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('songs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('artist');
            $table->string('album')->nullable();
            $table->string('genre')->nullable();
            $table->decimal('price', 8, 2);
            $table->string('trial_path');
            $table->string('full_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('songs');
    }
};
```

Membuat migrasi untuk tabel `purchases`.
```bash
php artisan make:model Purchase -m
```

Isi file migrasi `database/migrations/*_create_purchases_table.php` dengan kolom-kolom berikut, termasuk foreign keys:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('song_id')->constrained()->onDelete('cascade');
            $table->decimal('purchase_price', 8, 2);
            $table->timestamp('purchase_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
```

Modifikasi migrasi `database/migrations/*_create_users_table.php` untuk menambahkan kolom `role`.
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('role')->default('customer'); // Baris yang ditambahkan
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
```

Jalankan migrasi ulang secara bersih.
```bash
php artisan migrate:fresh
```

### **3. Konfigurasi Model Eloquent**

Perbarui `app/Models/User.php` untuk menambahkan `$fillable` 'role', method `isAdmin()`, dan relasi `purchases()`.
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Purchase; // Import Purchase model

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Ditambahkan
    ];

    /**
     * Check if the user has the 'admin' role.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Get the purchases for the user.
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
```

Perbarui `app/Models/Song.php` untuk menambahkan properti `$fillable` dan relasi `purchases()`.
```php
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
```

Perbarui `app/Models/Purchase.php` untuk menambahkan properti `$fillable`, `$casts`, dan relasi `user()`/`song()`.
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // Import User model
use App\Models\Song; // Import Song model

class Purchase extends Model
{
    use HasFactory;

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
```

### **4. Implementasi Hak Akses (Authorization)**

Definisikan Gate `access-admin` di `app/Providers/AppServiceProvider.php`.
```php
<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('access-admin', function (User $user) {
            return $user->isAdmin();
        });
    }
}
```

Buat middleware baru untuk admin.
```bash
php artisan make:middleware AdminMiddleware
```

Implementasikan logika di `app/Http/Middleware/AdminMiddleware.php`.
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth; // Ditambahkan

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
```

Daftarkan alias middleware `admin` di `bootstrap/app.php`.
```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class, // Ditambahkan
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
```

### **5. Fitur Publik dan Pelanggan**

Membuat Controller untuk Lagu.
```bash
php artisan make:controller SongController
```

Implementasi method `index()` di `app/Http/Controllers/SongController.php`.
```php
<?php

namespace App\Http\Controllers;

use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Ditambahkan

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
            $purchasedSongIds = $user->purchases->pluck('song_id');
        }
        
        return view('songs.index', compact('songs', 'purchasedSongIds'));
    }
}
```

Membuat Controller untuk Pembelian.
```bash
php artisan make:controller PurchaseController
```

Implementasi method `index()` dan `store()` di `app/Http/Controllers/PurchaseController.php`.
```php
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
        // Eager load song data with purchases
        $purchases = $user->purchases()->with('song')->get(); 

        return view('collection.index', compact('purchases'));
    }

    /**
     * Handle the purchase of a song.
     */
    public function store(Request $request, Song $song)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Anda harus login untuk membeli lagu.');
        }

        $user = Auth::user();

        if (Purchase::where('user_id', $user->id)->where('song_id', $song->id)->exists()) {
            return back()->with('error', 'Anda sudah memiliki lagu ini.');
        }

        Purchase::create([
            'user_id' => $user->id,
            'song_id' => $song->id,
            'purchase_price' => $song->price,
            'purchase_date' => now(),
        ]);

        return redirect()->route('songs.index')->with('success', 'Lagu berhasil dibeli!');
    }
}
```

Mendefinisikan rute untuk fitur publik dan pelanggan di `routes/web.php`.
```php
<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SongController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\Admin\SongController as AdminSongController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/songs', [SongController::class, 'index'])->name('songs.index');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // My Collection route
    Route::get('/my-collection', [PurchaseController::class, 'index'])->name('collection.index');

    // Purchase song route
    Route::post('/songs/{song}/purchase', [PurchaseController::class, 'store'])->name('songs.purchase');

    // Admin routes
    Route::middleware(['admin'])->group(function () {
        Route::get('/admin/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');

        // Admin Song Management
        Route::resource('admin/songs', AdminSongController::class)->names('admin.songs');

        // Admin User Management
        Route::resource('admin/users', AdminUserController::class)->except(['create', 'store', 'show'])->names('admin.users');

        // Admin Report Export
        Route::get('/admin/reports/sales', [AdminReportController::class, 'exportSales'])->name('admin.reports.sales');
    });
});

require __DIR__.'/auth.php';
```

Membuat view untuk daftar lagu `resources/views/songs/index.blade.php`.
```blade
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
```

Membuat view untuk koleksi lagu yang sudah dibeli `resources/views/collection/index.blade.php`.
```blade
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
```

### **6. Fitur Admin - CRUD dan Laporan**

Implementasi method `index`, `create`, `store`, `show`, `edit`, `update`, `destroy` di `app/Http/Controllers/Admin/SongController.php`.
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Song;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SongController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $songs = Song::all();
        return view('admin.dashboard', compact('songs')); // Mengarahkan ke dashboard admin
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
        // Not implemented in this context
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
            if ($song->trial_path) {
                Storage::disk('public')->delete($song->trial_path);
            }
            $validatedData['trial_path'] = $request->file('trial_file')->store('audio/trials', 'public');
        }

        if ($request->hasFile('full_file')) {
            if ($song->full_path) {
                Storage::disk('public')->delete($song->full_path);
            }
            $validatedData['full_path'] = $request->file('full_file')->store('audio/full', 'public');
        } else {
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
```

Implementasi method `index`, `edit`, `update`, `destroy` di `app/Http/Controllers/Admin/UserController.php`.
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Not used
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Not used
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Not used
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'string', Rule::in(['admin', 'customer'])],
        ]);

        $user->update($validatedData);

        return redirect()->route('admin.users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if (auth()->check() && auth()->user()->id === $user->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
```

Implementasi method `exportSales()` di `app/Http/Controllers/Admin/ReportController.php`.
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\User;
use App\Models\Song;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Export sales report as CSV.
     */
    public function exportSales(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="sales_report_'.now()->format('Ymd_His').'.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, ['ID Pembelian', 'Nama Pengguna', 'Email Pengguna', 'Judul Lagu', 'Artis Lagu', 'Harga Pembelian', 'Tanggal Pembelian']);

            Purchase::with(['user', 'song'])->chunk(1000, function ($purchases) use ($file) {
                foreach ($purchases as $purchase) {
                    fputcsv($file, [
                        $purchase->id,
                        $purchase->user->name ?? 'N/A',
                        $purchase->user->email ?? 'N/A',
                        $purchase->song->title ?? 'N/A',
                        $purchase->song->artist ?? 'N/A',
                        $purchase->purchase_price,
                        $purchase->purchase_date->format('Y-m-d H:i:s'),
                    ]);
                }
            });
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
```

Membuat view `resources/views/admin/dashboard.blade.php`.
```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Selamat datang di Panel Admin!</h3>
                    <p>Gunakan menu navigasi di atas untuk mengelola aplikasi.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

Membuat view `resources/views/admin/songs/index.blade.php`.
```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Kelola Lagu') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Daftar Lagu</h3>
                        <a href="{{ route('admin.songs.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Tambah Lagu Baru
                        </a>
                    </div>

                    @if (session('success'))
                        <div class="bg-green-100 dark:bg-green-700 border border-green-400 text-green-700 dark:text-green-100 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="bg-red-100 dark:bg-red-700 border border-red-400 text-red-700 dark:text-red-100 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    @if ($songs->isEmpty())
                        <p>Tidak ada lagu yang tersedia.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                                        <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Judul</th>
                                        <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Artis</th>
                                        <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Album</th>
                                        <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Harga</th>
                                        <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($songs as $song)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $song->id }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $song->title }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $song->artist }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $song->album ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">Rp {{ number_format($song->price, 2, ',', '.') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('admin.songs.edit', $song) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-600 me-3">Edit</a>
                                                <form action="{{ route('admin.songs.destroy', $song) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus lagu ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-600">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

Membuat view `resources/views/admin/songs/create.blade.php`.
```blade
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
```

Membuat view `resources/views/admin/songs/edit.blade.php`.
```blade
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
```

Membuat view `resources/views/admin/users/index.blade.php`.
```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Kelola Pengguna') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Daftar Pengguna</h3>
                    </div>

                    @if (session('success'))
                        <div class="bg-green-100 dark:bg-green-700 border border-green-400 text-green-700 dark:text-green-100 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="bg-red-100 dark:bg-red-700 border border-red-400 text-red-700 dark:text-red-100 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    @if ($users->isEmpty())
                        <p>Tidak ada pengguna yang tersedia.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                                        <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nama</th>
                                        <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                        <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Peran</th>
                                        <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($users as $user)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $user->id }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $user->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $user->email }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $user->role }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-600 me-3">Edit</a>
                                                @if (auth()->user()->id !== $user->id)
                                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-600">Hapus</button>
                                                    </form>
                                                @else
                                                    <span class="text-gray-400 dark:text-gray-600">Tidak dapat dihapus</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

Membuat view `resources/views/admin/users/edit.blade.php`.
```blade
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Pengguna') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Form Edit Pengguna</h3>

                    <form method="POST" action="{{ route('admin.users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <!-- Name -->
                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Nama')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $user->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email)" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Role -->
                        <div class="mb-4">
                            <x-input-label for="role" :value="__('Peran')" />
                            <select id="role" name="role" class="block mt-1 w-full border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="customer" @selected(old('role', $user->role) == 'customer')>Customer</option>
                                <option value="admin" @selected(old('role', $user->role) == 'admin')>Admin</option>
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Perbarui Pengguna') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
```

### **7. Finalisasi Navigasi dan Seeder**

File `resources/views/layouts/navigation.blade.php` setelah semua penyesuaian.
```blade
<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    @can('access-admin')
                        <a href="{{ route('admin.dashboard') }}">
                            <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}">
                            <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                        </a>
                    @endcan
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @can('access-admin')
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.songs.index')" :active="request()->routeIs('admin.songs.*')">
                            {{ __('Kelola Lagu') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                            {{ __('Kelola Pelanggan') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.reports.sales')" :active="request()->routeIs('admin.reports.*')">
                            {{ __('Laporan Penjualan') }}
                        </x-nav-link>
                    @else
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('songs.index')" :active="request()->routeIs('songs.index')">
                            {{ __('Daftar Lagu') }}
                        </x-nav-link>
                        <x-nav-link :href="route('collection.index')" :active="request()->routeIs('collection.index')">
                            {{ __('Koleksi Saya') }}
                        </x-nav-link>
                    @endcan
                </div>
            </div>

            <!-- Settings Dropdown -->
            @auth
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>
            @endauth

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @can('access-admin')
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.songs.index')" :active="request()->routeIs('admin.songs.*')">
                    {{ __('Kelola Lagu') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                    {{ __('Kelola Pelanggan') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.reports.sales')" :active="request()->routeIs('admin.reports.*')">
                    {{ __('Laporan Penjualan') }}
                </x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('songs.index')" :active="request()->routeIs('songs.index')">
                    {{ __('Daftar Lagu') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('collection.index')" :active="request()->routeIs('collection.index')">
                    {{ __('Koleksi Saya') }}
                </x-responsive-nav-link>
            @endcan
        </div>

        <!-- Responsive Settings Options -->
        @auth
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
        @endauth
    </div>
</nav>
```

Membuat seeder untuk lagu `database/seeders/SongSeeder.php`. Ini akan juga membuat file audio dummy di `storage/app/public/audio`.
```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Song;
use Illuminate\Support\Facades\Storage;

class SongSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!Storage::disk('public')->exists('audio')) {
            Storage::disk('public')->makeDirectory('audio');
        }

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
            'full_path' => null,
        ]);
    }
}
```

Membuat seeder utama `database/seeders/DatabaseSeeder.php` untuk memanggil `SongSeeder` dan membuat user admin/customer.
```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SongSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);
    }
}
```

Link storage untuk file publik.
```bash
php artisan storage:link
```

### **8. Pengujian Aplikasi**

1.  **Jalankan Server Laravel**:
    ```bash
    php artisan serve
    ```
2.  **Akses Aplikasi**: Buka browser web Anda dan navigasikan ke URL yang diberikan (misalnya, `http://127.0.0.1:8000`).
3.  **Uji Sebagai Pengunjung**: Lihat daftar lagu, putar trial.
4.  **Uji Sebagai Pelanggan**: Login dengan `test@example.com` / `password`. Coba beli lagu, lihat koleksi saya, dan edit profil.
5.  **Uji Sebagai Admin**: Login dengan `admin@example.com` / `password`. Akses menu Admin, kelola lagu/pengguna, dan ekspor laporan penjualan.


---
