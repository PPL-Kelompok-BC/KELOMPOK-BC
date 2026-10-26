# EcoChallenge Social Edition

Versi ini menambahkan pengalaman sosial pada GUI Laravel yang sama. Halaman utama komunitas adalah `#feed`. Ini tetap prototipe frontend, bukan jejaring sosial multiuser yang sudah online.

## Coba tanpa setup

Ekstrak seluruh paket ke folder baru agar pekerjaan lokal lama tetap tersimpan. Buka `preview/index.html`, lalu klik Masuk ke feed. Semua aset demo tersedia lokal dan tidak membutuhkan npm atau server. Untuk masuk langsung, tambahkan `#feed` pada URL file di browser.

## Yang bisa dicoba

- Feed scroll foto dengan tab Semua, Mengikuti, Video, dan Disimpan.
- Pencarian post, nama pengguna, judul challenge, dan filter kategori.
- Setiap post mempunyai tautan ke challenge asal, status verifikasi demo, dan nilai poin challenge.
- Like/unlike, komentar, simpan/batal simpan, follow/unfollow, dan tombol Ikuti balik untuk kontak yang sudah mengikuti profil demo.
- Profil, bio, daftar post, simpanan pribadi, hitungan pengikut/mengikuti, dan edit nama/bio lokal.
- Percakapan lokal dengan beberapa kontak contoh. Pesan yang ditulis hanya disimpan di browser, tanpa pengiriman keluar atau balasan otomatis.
- Bukti JPG/PNG maksimal 2 MB atau MP4/WebM maksimal 20 MB, dengan pratinjau.
- Persetujuan admin dan pilihan eksplisit membagikan media ke feed setelah disetujui. Bukti privat tidak menjadi post. Likes dan komentar tidak menambah poin.

Demo dimulai dengan tiga post foto stok dan empat profil fiktif. Belum ada video bawaan. Unggah MP4/WebM milik sendiri untuk mencoba tab Video. Pemutaran bergantung pada codec yang didukung browser; format MP4 H.264 umumnya cocok, tetapi bukan jaminan untuk semua video MP4.

Metadata disimpan dalam localStorage. Media unggahan hanya tersedia selama sesi halaman itu. Refresh menghilangkan media unggahan dan menampilkan placeholder, tetapi tidak menampilkan foto lain sebagai penggantinya. Foto stok tetap tersedia. Jalankan Reset demo jika ingin mengulang. Tab Mengikuti menampilkan akun yang benar-benar diikuti dalam state demo; tab Video hanya menampilkan post video.

## Alur post baru

1. Pilih Bagikan aksi, lalu buka challenge.
2. Pilih foto/video dan tulis keterangan.
3. Centang Bagikan ke feed setelah disetujui. Default-nya tidak dicentang.
4. Kirim bukti. Pengajuan berstatus menunggu dan belum mendapatkan poin/post publik.
5. Buka Simulasi admin di footer tanpa refresh. Setujui bukti.
6. Buka Feed aksi atau Profilku. Post baru memuat challenge asal. Poin bertambah hanya sekali.

## Update proyek Laravel lama tanpa mengulang npm

Paket ini memuat aset CSS/JS yang sudah dibangun. Untuk proyek EcoChallenge awal yang belum diubah anggota lain:

1. Hentikan `npm run dev` jika sedang berjalan, gunakan Ctrl+C di terminal Vite.
2. Ekstrak paket baru ke folder terpisah. Buka terminal PowerShell di folder yang berisi update-gui-windows.ps1.
3. Jalankan perintah ini dan sesuaikan lokasi proyek jika berbeda:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\update-gui-windows.ps1 -ProjectPath 'E:\Download\EcoChallenge-GUI-Starter\ecochallenge-gui\ecochallenge'
```

4. Pindah ke folder Laravel lama:

```powershell
cd 'E:\Download\EcoChallenge-GUI-Starter\ecochallenge-gui\ecochallenge'
php artisan view:clear
php artisan serve
```

5. Buka http://127.0.0.1:8000/#feed. Jika server sudah berjalan, tidak perlu menjalankan server kedua. Refresh dengan Ctrl+F5.

Updater mencadangkan file GUI lama sebelum menyalin versi baru. File `.env`, route yang ada, package.json, backend, dan database tidak diubah. File penanda Vite `public/hot` dipindahkan ke cadangan supaya Laravel menggunakan hasil build yang disertakan. Jangan menjalankan updater pada repository kelompok yang sudah memiliki perubahan GUI lain tanpa memeriksa dan menggabungkan perubahan tersebut terlebih dahulu.

Instalasi npm yang sebelumnya gagal masih perlu diperbaiki agar kamu dapat mengompilasi perubahan kode sendiri. Untuk melihat versi ini, hasil build yang disertakan sudah cukup. Coba `npm.cmd ci` di folder Laravel dan kirim error asli jika gagal.

## Batas implementasi

Belum tersedia akun nyata, koneksi antarpengguna, backend post/follow/chat, penyimpanan media server, notifikasi realtime, moderasi, pemblokiran, atau pelaporan konten. Statistik peserta lain adalah contoh. Jangan memakai GUI demo sebagai layanan publik. Sisi backend harus memverifikasi role, pemilik konten, file, dan privasi melalui Laravel; localStorage bukan sumber kebenaran.

Build Vite, sintaks JavaScript/ekspresi Alpine, serta pengujian state sosial diperiksa. Browser visual QA, PowerShell, Laravel, dan Laravel Dusk belum dijalankan di lingkungan pembuat paket.
