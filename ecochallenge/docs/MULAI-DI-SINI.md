# EcoChallenge GUI Social Edition

**Untuk fitur sosial dan update proyek yang sudah terpasang, baca SOCIAL-EDITION.md terlebih dahulu.**

Paket GUI proyek PPL Kelompok B-C. Stack: Laravel 12, Blade, Tailwind CSS 4, Alpine.js 3, Vite 7, dan ikon Lucide. Bahasa antarmuka Indonesia. Seluruh ilustrasi dibuat dari komposisi CSS dan ikon lokal, tanpa layanan gambar atau font eksternal.

## 1. Lihat hasil sebelum setup

Ekstrak seluruh ZIP. Buka `preview/index.html` dengan Brave, Chrome, atau Edge. Semua aset tersedia di folder preview. Jangan memindahkan index.html sendirian.

Preview ini merupakan frontend yang sama dengan view Laravel. Cari challenge, filter kategori, buka detail, unggah foto, lalu lihat riwayat. Pilih Simulasi admin di footer untuk menyetujui atau menolak klaim. Leaderboard dan dashboard akan berubah mengikuti hasilnya.

Nama, peringkat, poin awal, dan aktivitas adalah DATA DEMO. Tidak ada pendaftaran, login, API, database, atau otorisasi asli. Metadata klaim disimpan di localStorage browser; foto hanya menggunakan URL objek lokal selama halaman dibuka. Refresh menghilangkan foto. Klaim tanpa foto tidak bisa ditinjau. Jangan memakai data sensitif untuk demonstrasi.

## 2. Cek perangkat Windows

Buka terminal PowerShell di VS Code, lalu jalankan satu per satu:

```powershell
php -v
composer --version
node -v
npm -v
```

Diperlukan PHP 8.2+, Composer 2, dan Node.js 22.12+ (atau 20.19+). Laravel 12 dipilih agar kompatibel dengan PHP 8.2 pada XAMPP. PHP 8.2.12 dan Node 22.19 memenuhi batas versi ini, tetapi ekstensi PHP yang diminta Composer tetap harus tersedia.

Jika `php` tidak dikenali dan kamu memakai XAMPP pada lokasi default, untuk terminal itu saja jalankan:

```powershell
$env:Path = 'C:\xampp\php;' + $env:Path
php -v
```

Sesuaikan lokasi jika XAMPP ada di folder lain. Tidak perlu menyalakan Apache atau MySQL untuk GUI ini. Laravel dijalankan melalui `php artisan serve`.

## 3. Setup otomatis ke proyek BARU

Di PowerShell, pindah ke folder hasil ekstrak yang berisi file ini. Jalankan:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\setup-windows.ps1
```

Bypass hanya berlaku untuk proses tersebut, bukan perubahan kebijakan permanen. Script memeriksa alat, membuat folder Laravel baru bernama `ecochallenge`, menyalin GUI, menambahkan route, memakai file session/cache, memasang paket frontend, dan membangun aset. Internet diperlukan untuk Composer dan npm. Script berhenti jika folder target sudah ada agar tidak menimpa proyek lama.

Setelah selesai:

```powershell
cd ecochallenge
php artisan serve
```

Buka http://127.0.0.1:8000. Untuk mengubah kode dengan pembaruan otomatis, buka terminal kedua di folder Laravel yang sama:

```powershell
npm run dev
```

Biarkan kedua terminal berjalan. Tekan Ctrl+C untuk berhenti. Untuk presentasi tanpa Vite dev server, jalankan `npm run build` dan gunakan `php artisan serve` saja.

Jika Composer gagal, perbaiki error yang ditampilkan. Folder parsial tidak dihapus. Kamu bisa mencoba folder baru dengan `-ProjectName ecochallenge-baru` atau melanjutkan perbaikan di folder lama. Jangan menonaktifkan pemeriksaan keamanan Composer untuk memaksa instalasi.

## 4. Setup manual jika dibutuhkan

Dari folder paket:

```powershell
composer create-project 'laravel/laravel:^12.0' ecochallenge --no-interaction
Copy-Item .\overlay\* .\ecochallenge\ -Recurse -Force
cd ecochallenge
```

Di akhir `routes/web.php`, tambahkan sekali:

```php
require __DIR__.'/eco-gui.php';
```

Di `.env`, ubah:

```dotenv
APP_NAME=EcoChallenge
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

Kemudian jalankan:

```powershell
npm ci
npm run build
php artisan optimize:clear
php artisan serve
```

Jangan jalankan salinan massal overlay pada repository kelompok yang sudah berisi pekerjaan anggota lain. Untuk proyek yang sudah ada, integrasikan view, CSS, JavaScript, route, dan dependensinya melalui branch tersendiri. Package.json/Vite milik proyek lama perlu digabungkan, bukan langsung ditimpa.

## 5. File yang kamu edit untuk GUI

| File | Isi |
| --- | --- |
| resources/views/eco/app.blade.php | Struktur semua halaman, navbar, footer, dan modal upload |
| resources/css/app.css | Tailwind, warna, jarak, layout, dan aturan responsif |
| resources/js/app.js | State Alpine, navigasi, pencarian, upload, dan simulasi admin |
| resources/js/demo-state.js | Tujuh contoh challenge dan aturan poin/streak demo |
| routes/eco-gui.php | Route landing page Laravel |

Warna utama ada di `:root` dalam app.css. Halaman dinavigasi memakai hash (`#dashboard`, `#challenge`, dan sebagainya). Ini sengaja dibuat mudah dipresentasikan tanpa backend. Anggota backend dapat memecahnya menjadi view dan route terpisah.

## 6. Halaman yang sudah tersedia

1. Landing page dengan hero, contoh challenge, cara kerja, dan ajakan mencoba.
2. Dashboard poin, streak, jumlah aksi disetujui, dan antrean tinjauan.
3. Katalog tujuh challenge, pencarian, filter, dan kondisi hasil kosong.
4. Modal detail, petunjuk, penjelasan dampak, validasi foto, dan preview.
5. Riwayat dengan filter status serta detail dan alasan penolakan.
6. Leaderboard keseluruhan dan tujuh hari terakhir, termasuk peringkat pengguna.
7. Simulasi admin, persetujuan satu kali, dan penolakan dengan alasan wajib.

## 7. Alur demo untuk presentasi

1. Buka landing page, lalu pilih Temukan challenge.
2. Cari `tumbler` atau gunakan filter Sampah.
3. Buka detail, pilih JPG/PNG di bawah 2 MB, isi keterangan, dan kirim.
4. Tunjukkan status Menunggu tinjauan pada riwayat. Poin belum bertambah.
5. Buka Simulasi admin pada footer tanpa refresh halaman.
6. Setujui klaim, lalu buka dashboard dan leaderboard untuk melihat poin bertambah.
7. Coba challenge lain dengan penolakan beralasan.
8. Gunakan Reset demo untuk kembali ke kondisi awal.

Satu challenge hanya bisa diajukan sekali per tanggal Asia/Jakarta. Poin dihitung dari klaim disetujui. Streak memakai tanggal aktivitas disetujui, maksimal satu hari untuk banyak aksi pada tanggal yang sama. Data historis contoh mengikuti tanggal saat Reset demo ditekan. Poin bukan ukuran CO2.

## 8. Penyerahan ke anggota backend

Baca `INTEGRASI-BACKEND.md`. Validasi JavaScript dan tombol tersembunyi tidak memberikan keamanan backend. Semua validasi, otorisasi, transaksi poin, batas harian, dan privasi foto harus diterapkan ulang di Laravel.

GUI tidak menjalankan login atau memverifikasi foto secara otomatis. Simulasi admin sengaja dapat dibuka untuk presentasi dan harus dihapus/diganti dengan route admin terlindungi sebelum menjadi aplikasi asli.

## 9. Pemeriksaan yang dilakukan

Build Vite produksi dan bundle preview diperiksa. Aturan state demo diuji melalui Node: poin tidak diberikan saat pending, keputusan ganda tidak menambah poin, penolakan memerlukan alasan, klaim harian tidak boleh ganda, dan streak memakai tanggal berurutan.

Lingkungan pembuat paket tidak memiliki PHP, Composer, atau browser QA yang tersedia. Karena itu, instalasi Laravel, PowerShell, tampilan browser aktual, dan Laravel Dusk BELUM dijalankan. Jalankan pengecekan laptop di atas; jika muncul error, kirim screenshot lengkap terminal beserta perintah yang dijalankan. Proyek akhir tetap perlu pengujian Dusk sesuai instruksi dosen.

Sumber kompatibilitas: https://laravel.com/docs/12.x/releases
Integrasi Tailwind: https://tailwindcss.com/docs/installation/framework-guides/laravel/vite
Alpine: https://alpinejs.dev/start-here
Dusk: https://laravel.com/docs/12.x/dusk
