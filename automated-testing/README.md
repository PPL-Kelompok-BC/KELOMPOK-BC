# Automated Testing (Laravel Dusk) – EcoChallenge

Folder ini berisi tes otomatis **berbasis browser** untuk semua fitur EcoChallenge. Browser
(Chrome) **tampil di layar**, lalu mengetik, mengklik, dan berpindah halaman sendiri, jadi
jalannya tes bisa ditonton langsung.

Tes dipisah **per anggota tim**. Nama folder dan nama file mengikuti nama assignee dan nomor PBI
di Jira, jadi tiap orang bisa menjalankan tes bagiannya sendiri.

```
KELOMPOK-BC/
├─ ecochallenge/                 ← aplikasi Laravel
└─ automated-testing/            ← folder ini
   ├─ README.md                  ← panduan ini
   ├─ jalankan.ps1               ← pembantu: nyalakan server + jalankan tes
   ├─ bootstrap.php              ← agar PHPUnit mengenali kelas tes di folder ini
   ├─ .env.dusk.local.example    ← contoh konfigurasi tes
   └─ tests/
      ├─ FiturTestCase.php       ← dasar bersama (login, data uji, jeda, foto palsu)
      ├─ Jonathan/               ← PBI-03, PBI-04, PBI-19 s.d. PBI-22
      ├─ Fakhrezi/               ← PBI-23 s.d. PBI-25
      ├─ Najmi/                  ← PBI-12 s.d. PBI-14, layout dan dashboard mahasiswa
      ├─ Alexander/              ← PBI-15 s.d. PBI-18
      ├─ Hafidz/                 ← PBI-08 s.d. PBI-11
      └─ Seto/                   ← PBI-05 s.d. PBI-07
```

## Daftar berkas tes (sesuai Jira)

| Anggota | Berkas | Fitur yang diuji | PBI |
|---|---|---|---|
| **Jonathan** | `Jonathan_PBI03_RegistrasiTest.php` | Registrasi, pratinjau username, penguat kata sandi, validasi | 03 |
| | `Jonathan_PBI04_LoginTest.php` | Login, logout, peran, 403, akun nonaktif, batas 5 percobaan, dashboard admin | 04 |
| | `Jonathan_PBI19_PBI22_PoinLeaderboardTest.php` | Poin otomatis, leaderboard, penanda posisi, 50 besar, cache 2 detik | 19–22 |
| **Fakhrezi** | `Fakhrezi_PBI23_PBI25_ProfilTest.php` | Lihat profil, ubah nama/username, foto profil | 23–25 |
| **Najmi** | `Najmi_PBI12_PBI14_KatalogChallengeTest.php` | Daftar challenge aktif, filter kategori, detail challenge | 12–14 |
| | `Najmi_Layout_DashboardNavigasiTest.php` | Layout dasar, navbar, dashboard mahasiswa, menu HP | SCRUM-13 |
| **Alexander** | `Alexander_PBI15_PBI18_KlaimBuktiTest.php` | Unggah bukti, verifikasi berlapis, riwayat, kirim ulang | 15–18 |
| **Hafidz** | `Hafidz_PBI08_PBI11_KelolaChallengeTest.php` | Tambah, ubah, aktif/nonaktif challenge oleh admin | 08–11 |
| **Seto** | `Seto_PBI05_PBI07_KelolaKategoriTest.php` | Lihat, tambah, hapus kategori oleh admin | 05–07 |

### Tes tambahan berdasarkan test case Jira dan proposal

Berkas bernama `*_KasusEksepsi*Test.php` melengkapi tiap PBI dengan **test case di kartu Jira**
(PBI-03, 04, 12, 13, 14, 23, 24, 25: ID `TC-xx`) dan **kriteria penerimaan Normal/Eksepsi di
proposal** (PBI-05 s.d. 11, 15 s.d. 22, yang kartu Jira-nya belum berisi test case). Nama tiap
tes memuat PBI dan ID test case-nya, mis. `test_pbi04_tc09_akun_nonaktif_ditolak_dengan_pesan`.

| Anggota | Berkas tambahan | Acuan |
|---|---|---|
| **Jonathan** | `Jonathan_PBI03_PBI04_KasusEksepsiJiraTest.php` | Jira TC-01, TC-04 (PBI-03); TC-07, TC-09, TC-11 (PBI-04) |
| **Fakhrezi** | `Fakhrezi_PBI23_PBI25_KasusEksepsiJiraTest.php` | Jira TC-23-01..03, TC-24-01, TC-25-01, 02, 05 |
| **Najmi** | `Najmi_PBI12_PBI14_KasusEksepsiJiraTest.php` | Jira PBI-12 TC-01, 03, 04; PBI-13 TC-02, 04; PBI-14 TC-01, 03, 04 |
| **Alexander** | `Alexander_PBI15_PBI18_KasusEksepsiProposalTest.php` | Proposal PBI-15 s.d. PBI-18 (Normal/Eksepsi) |
| **Hafidz** | `Hafidz_PBI08_PBI11_KasusEksepsiProposalTest.php` | Proposal PBI-08 s.d. PBI-11 (Normal/Eksepsi) |
| **Seto** | `Seto_PBI05_PBI07_KasusEksepsiProposalTest.php` | Proposal PBI-05 s.d. PBI-07 (Normal/Eksepsi) |

Tabel lengkap "kriteria atau test case → nama tes" beserta temuan ada di
[`KESESUAIAN-JIRA-PROPOSAL.md`](KESESUAIAN-JIRA-PROPOSAL.md).

## Langkah menjalankan (pertama kali di komputermu)

Semua perintah dijalankan di **PowerShell**. Syarat: PHP 8.2+, Composer, Node, dan Google Chrome
sudah terpasang.

### 1. Masuk ke folder aplikasi dan ambil kode terbaru

```powershell
cd KELOMPOK-BC\ecochallenge
git pull origin main
composer install
npm install
npm run build
```

### 2. Pasang chromedriver yang cocok dengan versi Chrome-mu

```powershell
php artisan dusk:chrome-driver --detect
```

Perintah ini mengunduh driver ke `vendor\laravel\dusk\bin` (sekali saja; ulangi bila Chrome
diperbarui ke versi utama baru).

### 3. Konfigurasi tes (hanya untuk cara B / perintah manual)

**Kalau memakai `jalankan.ps1` (cara A), langkah ini tidak perlu.** Skrip itu menyalakan server
tes sendiri di port 8001 dengan basis data `database\dusk.sqlite` (dibuat otomatis), memaksa
`APP_URL`, `DB_CONNECTION`, dan `DB_DATABASE` lewat variabel lingkungan, lalu mengembalikan
semuanya seperti semula. Jadi tes tidak tersesat ke Apache/XAMPP (`http://localhost`, 404) atau ke
MySQL, walaupun `.env` di laptopmu memakai MySQL. Langsung ke langkah 4.

Untuk cara B, buat berkas konfigurasi tes sekali saja:

```powershell
Copy-Item .env .env.dusk.local
New-Item -ItemType File -Force database\dusk.sqlite | Out-Null
```

Lalu buka `ecochallenge\.env.dusk.local` dan pastikan baris berikut ada (contoh lengkapnya di
`automated-testing\.env.dusk.local.example`):

```
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=sqlite
DB_DATABASE=<path mutlak ke ecochallenge\database\dusk.sqlite>
DUSK_HEADLESS_DISABLED=true
DUSK_START_MAXIMIZED=true
```

> Dusk **mengosongkan** basis data tes pada setiap tes. Karena itu `DB_DATABASE` wajib mengarah
> ke berkas khusus tes (`dusk.sqlite`), bukan ke basis data demo/aslimu.

### 4. Cek cepat

```powershell
cd ..\automated-testing
.\jalankan.ps1 -Berkas Jonathan_PBI03_RegistrasiTest
```

Jendela Chrome akan terbuka dan mengisi formulir registrasi sendiri. Bila berjalan, setup selesai.

## Cara menjalankan tes

Ada dua cara. Keduanya membuka browser yang terlihat.

### A. Memakai skrip pembantu (paling mudah)

Skrip ini menyalakan server tes khusus di port 8001 (terpisah dari server pengembanganmu di port
8000), menjalankan tes, lalu mematikannya lagi. Tidak perlu `.env.dusk.local`. Jalankan dari
folder `automated-testing`:

```powershell
cd KELOMPOK-BC\automated-testing

.\jalankan.ps1                                    # SEMUA tes
.\jalankan.ps1 -Anggota Jonathan                  # semua tes milik satu anggota
.\jalankan.ps1 -Berkas Jonathan_PBI04_LoginTest   # satu berkas
.\jalankan.ps1 -Berkas Jonathan_PBI04_LoginTest -Filter test_admin_masuk_ke_dashboard_admin   # satu tes
.\jalankan.ps1 -Anggota Alexander -Jeda 1500      # lebih pelan, lebih mudah ditonton
.\jalankan.ps1 -TanpaTampil                       # tanpa jendela browser (cepat, untuk CI)
```

Nilai `-Anggota`: `Jonathan`, `Fakhrezi`, `Najmi`, `Alexander`, `Hafidz`, `Seto`.

### B. Perintah manual

Buka **dua terminal**.

**Terminal 1** – nyalakan server aplikasi (biarkan terbuka):

```powershell
cd KELOMPOK-BC\ecochallenge
php artisan serve --port=8000
```

**Terminal 2** – jalankan tes dari folder `ecochallenge`:

```powershell
cd KELOMPOK-BC\ecochallenge

# Semua tes
php artisan dusk --testsuite=Fitur

# Per anggota
php artisan dusk --testsuite=Jonathan
php artisan dusk --testsuite=Fakhrezi
php artisan dusk --testsuite=Najmi
php artisan dusk --testsuite=Alexander
php artisan dusk --testsuite=Hafidz
php artisan dusk --testsuite=Seto

# Per berkas
php artisan dusk ../automated-testing/tests/Jonathan/Jonathan_PBI03_RegistrasiTest.php
php artisan dusk ../automated-testing/tests/Jonathan/Jonathan_PBI04_LoginTest.php
php artisan dusk ../automated-testing/tests/Jonathan/Jonathan_PBI19_PBI22_PoinLeaderboardTest.php
php artisan dusk ../automated-testing/tests/Fakhrezi/Fakhrezi_PBI23_PBI25_ProfilTest.php
php artisan dusk ../automated-testing/tests/Najmi/Najmi_PBI12_PBI14_KatalogChallengeTest.php
php artisan dusk ../automated-testing/tests/Najmi/Najmi_Layout_DashboardNavigasiTest.php
php artisan dusk ../automated-testing/tests/Alexander/Alexander_PBI15_PBI18_KlaimBuktiTest.php
php artisan dusk ../automated-testing/tests/Hafidz/Hafidz_PBI08_PBI11_KelolaChallengeTest.php
php artisan dusk ../automated-testing/tests/Seto/Seto_PBI05_PBI07_KelolaKategoriTest.php

# Satu tes di dalam berkas
php artisan dusk ../automated-testing/tests/Jonathan/Jonathan_PBI04_LoginTest.php --filter=test_admin_masuk_ke_dashboard_admin
```

## Mengatur kecepatan dan tampilan

| Pengaturan | Cara | Keterangan |
|---|---|---|
| Jeda antarlangkah | `$env:DUSK_JEDA = "1500"` atau `-Jeda 1500` | Milidetik; bawaan 700. Makin besar makin pelan |
| Browser tampil / tersembunyi | `DUSK_HEADLESS_DISABLED=true` / `false` | `true` = jendela tampil |
| Pakai Brave atau Chromium lain | `DUSK_CHROME_BINARY=<path brave.exe>` di `.env.dusk.local` | Versi utama chromedriver harus sama dengan versi Chromium-nya |

Browser selalu memakai **profil sementara yang bersih**, jadi login dan riwayat browser harianmu
tidak tersentuh.

## Menambah tes baru

1. Taruh berkas di folder anggotanya, dengan nama `Nama_PBIxx_Fitur…Test.php` (nama dan nomor PBI
   mengikuti Jira), mis. `tests/Najmi/Najmi_PBI29_RingkasanDashboardTest.php`.
2. Namespace `Tests\Browser\Fitur\<Nama>`, kelas turunan `Tests\Browser\Fitur\FiturTestCase`, dan
   nama kelas sama persis dengan nama berkas.
3. Pakai pembantu di `FiturTestCase`: `mahasiswa()`, `admin()`, `kategori()`, `challenge()`,
   `masuk($browser, $user)`, `keluar($browser)`, `fotoPng($n)`, `jeda($browser)`.
4. Setelah berpindah halaman, tunggu teks dengan `waitForText(...)` (animasi halaman membuat teks
   baru terlihat setelah sepersekian detik); gunakan `assertSee(...)` untuk memastikan hasilnya.
   Perhatikan bahwa label ber-CSS `uppercase` terbaca sebagai huruf kapital oleh browser.

## Bila ada tes gagal

- Dusk menyimpan tangkapan layar kegagalan di `ecochallenge\tests\Browser\screenshots\` dan log
  konsol browser di `ecochallenge\tests\Browser\console\`.
- `Waited 5 seconds for text [Masuk.]`, browser membuka `localhost/login` dengan judul "404 Not
  Found", atau error `SQLSTATE ... mysql ... 3306`: tes tersesat ke Apache/XAMPP atau MySQL.
  Pakai `jalankan.ps1` versi terbaru (`git pull`), yang memaksa server tes di port 8001 dan SQLite.
  Untuk perintah manual, jalankan `php artisan config:clear` dan periksa `.env.dusk.local`.
- `Port 8001 sudah dipakai`: tutup program yang memakainya atau jalankan dengan `-Port 8002`.
- `Connection refused` pada perintah manual: server belum menyala di port 8000 (Terminal 1).
- `session not created ... version`: versi chromedriver tidak cocok dengan Chrome. Jalankan lagi
  `php artisan dusk:chrome-driver --detect`.
- Tes foto profil dilewati (skipped) bila ekstensi PHP **GD** belum aktif. Aktifkan `extension=gd`
  di `php.ini` untuk menjalankannya.
- Tes "klaim kedua di hari yang sama" dilewati di SQLite karena temuan bug: lihat bagian bawah.
- Tes bisa gagal bila fitur yang diuji diubah tim. Perbarui teks atau selektor di berkas tes terkait.

## Temuan dari tes ini

`ClaimController::alreadyClaimedToday()` membandingkan `claim_date` dengan string `Y-m-d`, padahal
SQLite menyimpan kolom itu sebagai `Y-m-d 00:00:00`. Akibatnya cek "satu klaim per challenge per
hari" meleset di SQLite dan klaim kedua menabrak batas unik basis data (HTTP 500) alih-alih
menampilkan pesan. Di MySQL (produksi) kolom `DATE` tidak bermasalah. Perbaikannya satu baris:
ganti `->where('claim_date', today()->toDateString())` dengan
`->whereDate('claim_date', today())`.
