# Integrasi GUI EcoChallenge dengan backend kelompok

Paket ini berisi GUI dan state demo. Implementasi server harus tetap mengikuti keputusan kelompok. Nama berikut merupakan usulan kontrak, bukan API yang sudah tersedia.

| Modul | Integrasi yang dibutuhkan |
| --- | --- |
| Katalog | Ambil challenge aktif dari database beserta kategori dan ketentuan bukti |
| Pengajuan | POST multipart dengan challenge_id, proof, dan note; tanggal aktivitas ditentukan server |
| Riwayat | Ambil pengajuan milik pengguna terautentikasi; jangan menerima user_id bebas dari klien |
| Admin | Otorisasi role sebelum menampilkan bukti atau memutuskan klaim |
| Keputusan | Transaksi database yang mengunci klaim pending dan hanya membuat satu transaksi poin |
| Streak | Hitung ulang tanggal aktivitas yang disetujui ketika keputusan berubah |
| Leaderboard | Gunakan poin tersimpan secara sah; jangan percaya localStorage |

Usulan tabel: users, categories, challenges, submissions, point_transactions. Simpan points_snapshot pada submissions. Unique constraint submissions(user_id, challenge_id, activity_date); unique submission_id pada point_transactions. Challenge yang telah memiliki riwayat diarsipkan. Tentukan aturan penolakan/perbaikan bersama tim; demo ini membatasi satu pengajuan per challenge per hari tanpa kirim ulang.

Bukti harus disimpan privat. Akses melalui controller yang memeriksa pemilik/admin, bukan URL storage publik. Validasi MIME, isi berkas, ukuran 2 MB, panjang keterangan, challenge aktif, CSRF, session, dan kepemilikan dilakukan di server. Poin dan role tidak boleh berasal dari input browser. Hapus daftar peserta contoh, fallback lokal, serta tautan admin publik ketika backend nyata terintegrasi.

Alpine memakai x-text untuk isi pengguna. Pertahankan escaping Blade dan jangan mengganti dengan raw HTML untuk keterangan, nama file, atau alasan penolakan.

Pada proyek akhir, gunakan Laravel Dusk untuk alur katalog/filter, klaim valid, berkas salah/terlalu besar, klaim ganda, akses lintas akun, akses admin, persetujuan ganda, penolakan beralasan, serta pembaruan dashboard/leaderboard. Lengkapi dengan pengujian backend untuk transaksi bersamaan dan batas tanggal. Paket GUI ini tidak menggantikan testing tersebut.

## Tambahan Social Edition

Usulan tabel tambahan: posts (submission_id unik, user_id, caption, visibility), follows (follower_id, followed_id unik), likes (user_id, post_id unik), bookmarks (user_id, post_id unik), comments, conversations, conversation_participants, messages. Seluruh relasi dan operasi memerlukan otorisasi. Follow sendiri dilarang; likes/bookmarks harus idempotent. Pesan hanya dapat dibaca anggota percakapan. Terapkan moderasi dan opsi lapor/blokir sebelum dibuka ke pengguna nyata.

Alur demo mempublikasikan post hanya bila submission disetujui dan pemilik memilih berbagi. Bukti privat tetap privat. Media publik perlu penyajian terpisah dari bukti asli, dengan izin pemilik serta pemeriksaan ukuran, MIME dan codec. Jangan mempublikasikan seluruh storage bukti. Unggahan video demo memiliki batas 20 MB; backend harus menerapkan batasnya sendiri. Likes, jumlah follower, komentar, dan chat tidak mempengaruhi poin challenge.

Pisahkan validasi bukti dari publikasi sosial. Tambahkan pengujian Dusk untuk feed filters, profil, follow/unfollow, like/unlike, komentar, bookmark, chat antarpeserta yang berhak, dan bukti privat yang tidak muncul di feed.
