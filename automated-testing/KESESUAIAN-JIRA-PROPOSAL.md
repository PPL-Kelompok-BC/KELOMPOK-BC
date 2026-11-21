# Kesesuaian tes Dusk dengan Jira dan proposal

Acuan: (1) test case `TC-xx` di kartu Jira bila ada, (2) kriteria penerimaan **Normal / Eksepsi**
di proposal (Tabel PBI) untuk PBI yang kartu Jira-nya belum berisi test case. Kolom "Tes" memuat
nama metode; berkasnya ada di folder anggota yang bersangkutan.

Keterangan status: ✅ ada tesnya dan lulus · ⏭ dilewati karena temuan atau prasyarat (lihat catatan)
· 🧪 hanya ada di PHPUnit (tidak relevan untuk browser).

## Jonathan

| PBI | Test case / kriteria | Tes | Status |
|---|---|---|---|
| PBI-03 | TC-01 registrasi valid → dashboard, kata sandi ter-hash | `test_registrasi_berhasil_lalu_masuk_ke_dashboard_mahasiswa`, `test_pbi03_tc01_akun_tersimpan_dengan_kata_sandi_ter_hash_dan_streak_awal_nol` | ✅ |
| PBI-03 | TC-01 streak awal bernilai 0 | `test_pbi03_tc01_baris_streak_awal_nol_dibuat_saat_registrasi` | ✅ (Temuan 1 sudah diperbaiki) |
| PBI-03 | TC-02 email dipakai, TC-03 username dipakai | `test_username_dan_email_yang_sudah_dipakai_ditolak` | ✅ |
| PBI-03 | TC-04 kata sandi 5 karakter / konfirmasi beda | `test_pbi03_tc04_*` (2 tes) | ✅ |
| PBI-04 | TC-05, TC-06 login mahasiswa dan admin | `test_mahasiswa_masuk_ke_dashboard_mahasiswa`, `test_admin_masuk_ke_dashboard_admin` | ✅ |
| PBI-04 | TC-07 kredensial salah / email tidak terdaftar | `test_kata_sandi_salah_*`, `test_pbi04_tc07_*` | ✅ |
| PBI-04 | TC-08 pembatasan login (percobaan ke-6) | `test_setelah_lima_kali_gagal_login_ditahan_dengan_hitung_mundur` | ✅ |
| PBI-04 | TC-09 akun nonaktif | `test_akun_nonaktif_tidak_bisa_masuk`, `test_pbi04_tc09_*` | ✅ (Temuan 2) |
| PBI-04 | TC-10 logout | `test_keluar_mengakhiri_sesi_dan_dashboard_kembali_meminta_login` | ✅ |
| PBI-04 | TC-11 tamu ke halaman terproteksi | `test_pbi04_tc11_*` | ✅ |
| PBI-04 | TC-12 mahasiswa ke halaman admin (403) | `test_mahasiswa_tidak_boleh_membuka_halaman_admin` | ✅ |
| PBI-19 | Normal: klaim 10 poin menaikkan 20 → 30 | `test_poin_bertambah_otomatis_dan_peringkat_naik_setelah_klaim_disetujui` | ✅ |
| PBI-19 | Eksepsi: penyimpanan gagal, poin tidak berubah | – | 🧪 `ClaimPointsTest` |
| PBI-20 | Normal: poin tertinggi urutan pertama; Eksepsi: akun nonaktif tidak tampil | `test_leaderboard_mengurutkan_berdasarkan_poin_dan_menandai_posisi_sendiri` | ✅ |
| PBI-21 | Normal: baris sendiri ditandai + username; Eksepsi: di luar 50 besar | `test_leaderboard_mengurutkan_*`, `test_pengguna_di_luar_50_besar_tetap_melihat_barisnya_sendiri` | ✅ |
| PBI-22 | Normal: poin baru langsung tampil | `test_perubahan_poin_langsung_tampil_setelah_leaderboard_diperbarui` | ✅ |
| PBI-22 | Eksepsi: cache gagal, hitung dari basis data | – | 🧪 `LeaderboardTest` |

## Fakhrezi

| PBI | Test case | Tes | Status |
|---|---|---|---|
| PBI-23 | TC-23-01 profil lengkap dengan foto | `test_pbi23_tc01_dan_pbi25_tc01_*` | ✅ |
| PBI-23 | TC-23-02 tamu ke profil → login | `test_pbi23_tc02_*` | ✅ |
| PBI-23 | TC-23-03 tanpa foto → avatar inisial | `test_pbi23_tc03_*` | ✅ |
| PBI-23 | TC-23-04 hanya data milik sendiri | – | 🧪 PHPUnit |
| PBI-24 | TC-24-01 ubah nama, username, email + leaderboard | `test_pbi24_tc01_*` | ✅ |
| PBI-24 | TC-24-02 username milik orang lain ditolak | `test_username_yang_sudah_dipakai_orang_lain_ditolak` | ✅ |
| PBI-24 | TC-24-03..05 | – | 🧪 PHPUnit |
| PBI-25 | TC-25-01 foto JPG tersimpan dan tampil | `test_pbi25_tc01_*` (di tes PBI-23 gabungan) | ✅ |
| PBI-25 | TC-25-02 berkas bukan JPG/PNG ditolak, foto lama tetap | `test_pbi25_tc02_*` | ✅ |
| PBI-25 | TC-25-05 foto lebih dari 2 MB ditolak | `test_pbi25_tc05_*` | ✅ |
| PBI-25 | TC-25-03, 04, 06 | – | 🧪 PHPUnit |

## Najmi

| PBI | Test case | Tes | Status |
|---|---|---|---|
| PBI-12 | TC-01 kartu memuat kategori, kesulitan, poin, emisi | `test_pbi12_tc01_*` | ✅ |
| PBI-12 | TC-02 challenge nonaktif disembunyikan | `test_katalog_hanya_menampilkan_challenge_aktif` | ✅ |
| PBI-12 | TC-03 belum ada challenge aktif | `test_pbi12_tc03_*` | ✅ |
| PBI-12 | TC-04 tamu → login | `test_pbi12_tc04_*` | ✅ |
| PBI-13 | TC-01 filter kategori, TC-03 tombol Semua | `test_filter_kategori_menyaring_dan_semua_mengembalikan_daftar` | ✅ |
| PBI-13 | TC-02 kategori kosong | `test_pbi13_tc02_*` | ✅ |
| PBI-13 | TC-04 `?kategori=abc` | `test_pbi13_tc04_*` | ✅ |
| PBI-14 | TC-01 detail lengkap | `test_pbi14_tc01_*`, `test_detail_challenge_menampilkan_kriteria_bukti_dan_tombol_kirim_bukti` | ✅ |
| PBI-14 | TC-02 nonaktif → 404, TC-03 tidak ada → 404 | `test_challenge_nonaktif_tidak_bisa_dibuka_lewat_alamatnya`, `test_pbi14_tc03_*` | ✅ |
| PBI-14 | TC-04 kriteria kosong → pesan pengganti | `test_pbi14_tc04_*` | ✅ |
| Layout (SCRUM-13) | navbar, dashboard, menu HP | `Najmi_Layout_DashboardNavigasiTest` | ✅ |

## Alexander (acuan proposal; kartu SCRUM-55..58 belum berisi test case)

| PBI | Kriteria proposal | Tes | Status |
|---|---|---|---|
| PBI-15 | Normal: pilih foto, kirim, halaman hasil klaim (≤ 3 langkah) | `test_pbi15_normal_*` | ✅ |
| PBI-15 | Eksepsi: PDF atau di atas 2 MB ditolak dengan alasan jelas | `test_pbi15_eksepsi_*` (2 tes), `test_berkas_selain_jpg_png_ditolak` | ✅ |
| PBI-16 | Normal: foto dengan EXIF hari ini diterima | `test_pbi16_normal_*` | ✅ (Temuan 3 sudah diperbaiki) |
| PBI-16 | Eksepsi: hash foto sama ditolak | `test_foto_yang_sama_tidak_bisa_dipakai_dua_kali` | ✅ (Temuan 4) |
| PBI-17 | Normal: pemilik membuka foto dari riwayat | `test_pbi17_normal_*` | ✅ |
| PBI-17 | Eksepsi: mahasiswa lain tidak bisa membuka URL foto | `test_pbi17_eksepsi_*` | ✅ (Temuan 5) |
| PBI-18 | Normal: klaim kedua di hari sama ditolak + kirim ulang | `test_klaim_kedua_*` (dilewati di SQLite), `test_klaim_ditolak_menampilkan_alasan_dan_bisa_dikirim_ulang` | ⏭ Temuan 6 |
| PBI-18 | Eksepsi: foto tanpa EXIF tetap diterima dan ditandai tinjau | `test_pbi18_eksepsi_*` | ✅ |

## Hafidz (acuan proposal; kartu SCRUM-27..30 belum berisi test case)

| PBI | Kriteria proposal | Tes | Status |
|---|---|---|---|
| PBI-08 | Normal: tabel dengan kolom dan filter status; Eksepsi: pesan "Belum ada challenge" | `test_pbi08_normal_*`, `test_pbi08_eksepsi_*` | ✅ |
| PBI-09 | Normal: challenge baru tampil di katalog | `test_pbi09_normal_*` | ✅ |
| PBI-09 | Eksepsi: kriteria kosong / emisi negatif ditolak | `test_pbi09_eksepsi_*` | ✅ |
| PBI-10 | Normal: ubah challenge tersimpan | `test_admin_mengubah_challenge_lewat_halaman_edit` | ✅ |
| PBI-10 | Eksepsi: poin nol / negatif ditolak | `test_pbi10_eksepsi_*` | ✅ |
| PBI-11 | Normal: nonaktif hilang dari katalog | `test_pbi11_normal_*`, `test_admin_menonaktifkan_lalu_mengaktifkan_kembali_challenge` | ✅ |
| PBI-11 | Eksepsi: klaim pada challenge nonaktif ditolak | `test_pbi11_eksepsi_*` | ✅ (Temuan 7) |

## Seto (acuan proposal; kartu SCRUM-10, 25, 26 belum berisi test case)

| PBI | Kriteria proposal | Tes | Status |
|---|---|---|---|
| PBI-05 | Normal: nama dan jumlah challenge tiap kategori; Eksepsi: mahasiswa → 403 | `test_pbi05_normal_*`, `test_admin_melihat_*`, `test_mahasiswa_tidak_bisa_membuka_halaman_kelola_kategori` | ✅ |
| PBI-06 | Normal: kategori baru muncul di daftar | `test_admin_menambah_dan_menghapus_kategori_yang_belum_dipakai` | ✅ |
| PBI-06 | Eksepsi: nama yang sudah ada ditolak dengan pesan | `test_pbi06_eksepsi_*` | ✅ |
| PBI-07 | Normal: ubah nama tampil di daftar dan katalog | `test_pbi07_normal_*` | ✅ |
| PBI-07 | Eksepsi: hapus kategori yang dipakai challenge ditolak | `test_pbi07_eksepsi_*` | ✅ |

## Temuan (perbedaan aplikasi dengan Jira atau proposal)

Tes ditulis mengikuti perilaku aplikasi yang berjalan; perbedaan di bawah ini dicatat agar tim bisa
memutuskan apakah aplikasi atau dokumennya yang disesuaikan. Temuan yang sudah diperbaiki ditandai beserta nomor PR-nya.

1. **Streak awal (Jira PBI-03 TC-01): SUDAH DIPERBAIKI (PR #29).** Registrasi kini membuat baris `streaks`
   bernilai 0 dalam satu transaksi dengan akun, sesuai UC-01 langkah 5 di proposal.
2. **Pesan akun nonaktif (Jira PBI-04 TC-09): SUDAH DIPERBAIKI (PR #29).** Aplikasi kini memakai pesan proposal
   UC-02 alur 2c: "Akun Anda dinonaktifkan, hubungi admin".
3. **Tanda tinjau (proposal PBI-16): SUDAH DIPERBAIKI (PR #28).** Sesuai UC-06, klaim yang lolos verifikasi
   langsung diterima dan poin bertambah saat itu juga; `needs_review` hanya `true` untuk foto tanpa EXIF
   (alur 5b).
4. **Pesan foto kembar (proposal PBI-16):** proposal: "Foto ini sudah pernah dipakai untuk klaim
   lain". Aplikasi: "Foto ini sudah pernah dipakai. Gunakan foto lain."
5. **Foto milik orang lain (proposal PBI-17):** proposal: respons 403. Aplikasi: 404 (menyembunyikan
   keberadaan klaim).
6. **Satu klaim per hari (proposal PBI-18):** proposal: pesan "Kamu sudah mengklaim challenge ini
   hari ini". Aplikasi: "Anda sudah mengirim bukti untuk challenge ini hari ini." Selain itu, di
   SQLite pengecekan `alreadyClaimedToday()` meleset (`claim_date` tersimpan `Y-m-d 00:00:00`), sehingga
   klaim kedua menabrak batas unik (HTTP 500). Di MySQL tidak terjadi. Perbaikan: `whereDate('claim_date', today())`.
7. **Klaim pada challenge nonaktif (proposal PBI-11):** proposal: pesan "Challenge ini sudah tidak
   aktif". Aplikasi: halaman 404.
