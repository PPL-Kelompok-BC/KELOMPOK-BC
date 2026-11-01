# EcoChallenge

Website gamifikasi aksi ramah lingkungan untuk mahasiswa Telkom University.
Proyek PPL Kelompok B-C. Laravel 12, Blade, Tailwind CSS, Alpine.js, MySQL.

## Kebutuhan

- PHP 8.2 atau lebih baru (ekstensi `zip` aktif di `php.ini`)
- Composer 2
- Node.js 20.19+ atau 22.12+, dan npm
- MySQL atau MariaDB (XAMPP atau Laragon)
- Google Chrome (untuk Laravel Dusk)

## Instalasi

1. Clone repository, lalu masuk ke folder Laravel:
   ```
   git clone https://github.com/PPL-Kelompok-BC/KELOMPOK-BC.git
   cd KELOMPOK-BC/ecochallenge
   ```
2. Pasang dependensi:
   ```
   composer install
   npm install
   ```
3. Salin konfigurasi dan buat kunci aplikasi:
   ```
   copy .env.example .env
   php artisan key:generate
   ```
4. Nyalakan MySQL, lalu buat dua database: `ecochallenge` dan `ecochallenge_test`
   (collation `utf8mb4_unicode_ci`).
5. Sesuaikan `DB_*` di `.env` bila perlu (bawaan: user `root`, tanpa kata sandi).
6. Jalankan migrasi dan build aset:
   ```
   php artisan migrate
   npm run build
   ```

## Menjalankan

```
php artisan serve
```

Buka http://127.0.0.1:8000. Untuk pengembangan dengan pembaruan otomatis,
jalankan `npm run dev` di terminal kedua.

## Pengujian (Laravel Dusk)

1. Salin `.env.dusk.local` dari anggota tim atau buat sendiri dengan
   `APP_KEY` yang sama seperti `.env` dan `DB_DATABASE=ecochallenge_test`.
   File ini tidak di-commit.
2. Unduh ChromeDriver sekali: `php artisan dusk:chrome-driver --detect`
3. Nyalakan server di satu terminal (`php artisan serve`),
   lalu di terminal lain jalankan:
   ```
   php artisan dusk
   ```

## Aturan Git

- Branch kerja: `feature/<nama>-pbi-<nomor>-<singkat>`
- Dilarang push langsung ke `main`. Wajib pull request dan minimal 1 persetujuan.
- Jangan commit `.env`, `.env.dusk.local`, `vendor/`, atau `node_modules/`.