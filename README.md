# Instruksi Menjalankan Proyek Musicpedia dari GitHub

Dokumen ini menjelaskan cara mengambil (clone) dan menjalankan proyek aplikasi `musicpedia` di komputer lokal untuk keperluan praktikum. Instruksi dan penjelasan kode ada di file **JOBSHEET.md**.

## Prasyarat

Pastikan perangkat Kalian telah terinstal perangkat lunak berikut:

1.  **Git**: Untuk mengambil kode dari repositori GitHub.
2.  **PHP**: Versi 8.2 atau yang lebih baru.
3.  **Composer**: Dependency manager untuk PHP.
4.  **Node.js & NPM**: Untuk mengelola dan mengkompilasi aset frontend (JavaScript & CSS).
5.  **Web Server Lokal**: Seperti Laragon, XAMPP, atau sejenisnya.

---

## Langkah-Langkah Instalasi

### 1. Clone Proyek dari GitHub

Buka terminal atau command prompt, arahkan ke direktori tempat Kalian ingin menyimpan proyek, lalu jalankan perintah berikut:

```bash
git clone https://github.com/madasepandri/musicpedia.git
```

Setelah selesai, masuk ke direktori proyek yang baru dibuat:

```bash
cd musicpedia
```

### 2. Instal Dependensi Proyek

Proyek Laravel memiliki dua jenis dependensi: backend (PHP) yang dikelola oleh Composer, dan frontend (JavaScript) yang dikelola oleh NPM. Jadi kita harus menginstal dependensi keudanya dengan cara sebagai berikut.

- **Instal dependensi PHP:**
  ```bash
  composer install
  ```

- **Instal dependensi JavaScript:**
  ```bash
  npm install
  ```

### 3. Konfigurasi Lingkungan (Environment)

Setiap instalasi Laravel memerlukan file konfigurasi lingkungan sendiri yang disebut `.env`. File ini tidak diunggah ke GitHub demi keamanan. Jadi kita buat dulu satu dengan perintah di bawah ini.

- **Salin file contoh `.env`:**
  -   Untuk Windows:
      ```cmd
      copy .env.example .env
      ```
  -   Untuk macOS/Linux:
      ```bash
      cp .env.example .env
      ```

- **Generate Kunci Aplikasi (Application Key):**
  Laravel menggunakan kunci ini untuk mengenkripsi data. Jalankan perintah berikut:
  ```bash
  php artisan key:generate
  ```

### 4. Setup Database dan Data Awal

Proyek ini dikonfigurasi untuk menggunakan **SQLite** secara default untuk kemudahan, sehingga Kalian tidak perlu membuat database di MySQL atau sejenisnya.

- **Buat file database SQLite:**
  Buat sebuah file kosong bernama `database.sqlite` di dalam direktori `database`.
  -   Untuk Windows (command prompt):
      ```cmd
      echo. > database\database.sqlite
      ```
  -   Untuk macOS/Linux:
      ```bash
      touch database/database.sqlite
      ```

- **Jalankan Migrasi dan Seeder:**
  Perintah `migrate:fresh` akan membuat semua tabel dalam database, dan flag `--seed` akan mengisi tabel tersebut dengan data awal (termasuk akun admin dan pelanggan).
  ```bash
  php artisan migrate:fresh --seed
  ```

### 5. Kompilasi Aset Frontend

Jalankan perintah berikut untuk mengkompilasi file CSS dan JavaScript.
```bash
npm run dev
```

### 6. Buat Symbolic Link untuk Storage

Agar file yang diunggah (seperti file audio lagu) dapat diakses dari web, kita perlu membuat "shortcut" atau tautan simbolis dari direktori `public` ke direktori `storage`.

```bash
php artisan storage:link
```

### 7. Jalankan Server Pengembangan

Sekarang, aplikasi Kalian siap dijalankan. Gunakan perintah `serve` dari Artisan:

```bash
php artisan serve
```

Buka browser Kalian dan kunjungi alamat yang ditampilkan di terminal (biasanya `http://127.0.0.1:8000`).

---

## Informasi Akun untuk Pengujian

Kalian dapat langsung login menggunakan akun yang telah dibuat oleh Seeder:

-   **Akun Admin:**
    -   **Email:** `admin@example.com`
    -   **Password:** `password`

-   **Akun Pelanggan:**
    -   **Email:** `test@example.com`
    -   **Password:** `password`


