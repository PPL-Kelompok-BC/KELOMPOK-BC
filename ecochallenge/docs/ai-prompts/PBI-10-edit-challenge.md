\# AI Prompt Documentation — PBI-10 Edit Challenge



\## Informasi PBI

\- \*\*ID:\*\* PBI-10

\- \*\*Jira Issue:\*\* SCRUM-29

\- \*\*Fitur:\*\* Kelola Challenge

\- \*\*User Story:\*\* Sebagai admin, saya ingin mengubah data challenge agar kesalahan informasi seperti judul, poin, dan kriteria bukti dapat diperbaiki.



\## Prompt yang Digunakan



Saya sedang mengembangkan aplikasi EcoChallenge menggunakan Laravel. Bantu saya mengimplementasikan PBI-10, yaitu fitur mengubah data challenge oleh admin.



Kebutuhan fitur:

1\. Admin dapat membuka form edit challenge dari halaman daftar challenge.

2\. Form menampilkan data challenge yang sudah tersimpan.

3\. Admin dapat mengubah judul, deskripsi, kategori, tingkat kesulitan, poin, kriteria bukti, estimasi emisi CO2e, dan sumber emisi.

4\. Sistem melakukan validasi terhadap data yang dikirim.

5\. Poin challenge wajib berupa bilangan bulat minimal 1. Nilai nol dan negatif harus ditolak.

6\. Setelah berhasil disimpan, admin diarahkan kembali ke halaman daftar challenge dengan pesan keberhasilan.

7\. Perubahan poin challenge tidak boleh mengubah nilai `points\_awarded` dan `co2e\_awarded\_gram` pada klaim yang sudah ada.

8\. Fitur harus dilengkapi PHPUnit Feature Test dan Laravel Dusk Browser Test.



Berikan implementasi yang sesuai dengan struktur project Laravel yang sudah ada. Jelaskan perubahan setiap file dan berikan kode lengkap agar mudah diterapkan dan diuji.



\## Implementasi

\- Menambahkan method `edit()` dan `update()` pada `AdminChallengeController`.

\- Menambahkan relasi challenge dengan claims.

\- Menambahkan model `Claim`.

\- Menambahkan route edit dan update.

\- Membuat halaman edit challenge.

\- Menambahkan tombol Edit pada halaman daftar challenge.

\- Membuat PHPUnit Feature Test untuk validasi dan konsistensi data klaim.

\- Membuat Laravel Dusk Browser Test untuk menguji proses edit melalui browser.



\## Hasil Pengujian

\- PHPUnit Feature Test: 5 test, 22 assertions.

\- Laravel Dusk Browser Test: 1 test, 6 assertions.

\- Tidak ditemukan assertion yang gagal.

\- Pengujian menampilkan peringatan deprecation terkait `PDO::MYSQL\_ATTR\_SSL\_CA` pada PHP 8.5.

