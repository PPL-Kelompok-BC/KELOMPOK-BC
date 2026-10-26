# EcoChallenge - GUI Social Edition (PPL Kelompok B-C)

Aplikasi tantangan aksi ramah lingkungan berbasis web yang dirancang untuk mahasiswa (SDG 13 - Climate Action). Dilengkapi dengan antarmuka sosial (feed aksi, profil, leaderboard, pesan, riwayat klaim, dan simulasi persetujuan admin).

---

## 🛠️ Tech Stack
- **Framework:** Laravel 12
- **Templating:** Blade
- **Styling:** Tailwind CSS 4
- **Reactivity & State:** Alpine.js 3
- **Icons:** Lucide Icons
- **Bundler:** Vite 7

---

## 🚀 Panduan Menjalankan Proyek

### 1. Kebutuhan Sistem
Pastikan telah terpasang di perangkat Anda:
- **PHP** >= 8.2 (disarankan PHP 8.2+)
- **Composer** 2.x
- **Node.js** >= 20.19 / 22.12+ & **npm**

### 2. Instalasi Dependensi
Clone repositori ini, lalu masuk ke direktori proyek:
```bash
git clone https://github.com/jonathanongithubs/PPLSEM5.git
cd PPLSEM5
```

Salin file environment:
```bash
cp .env.example .env
```

Install paket PHP (Composer):
```bash
composer install
```

Generate application key:
```bash
php artisan key:generate
```

Install paket frontend (Node & npm):
```bash
npm install
```

Build aset frontend:
```bash
npm run build
```

### 3. Menjalankan Server
Jalankan server pengembangan Laravel:
```bash
php artisan serve
```
Akses aplikasi melalui browser di: **http://127.0.0.1:8000**

Untuk pengembangan aktif dengan hot module reloading (HMR):
```bash
npm run dev
```

---

## 📚 Dokumentasi Lanjutan
Dokumentasi teknis dan panduan integrasi backend tersedia di folder [`docs/`](docs/):
- [`docs/INTEGRASI-BACKEND.md`](docs/INTEGRASI-BACKEND.md): Panduan skema database, otorisasi, dan integrasi backend kelompok.
- [`docs/SOCIAL-EDITION.md`](docs/SOCIAL-EDITION.md): Penjelasan fitur-fitur edisi sosial (feed, komentar, profil, dll).
- [`docs/MULAI-DI-SINI.md`](docs/MULAI-DI-SINI.md): Panduan awal setup dan detail alur presentasi.
- [`docs/ASSET-CREDITS.md`](docs/ASSET-CREDITS.md): Sumber kredit aset gambar dan ikon.
