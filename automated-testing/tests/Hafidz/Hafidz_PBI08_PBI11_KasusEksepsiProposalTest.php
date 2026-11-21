<?php

namespace Tests\Browser\Fitur\Hafidz;

use App\Models\Challenge;
use Laravel\Dusk\Browser;
use Tests\Browser\Fitur\FiturTestCase;

/**
 * Hafidz – melengkapi kriteria penerimaan proposal untuk PBI-08 s.d. PBI-11
 * (kartu Jira SCRUM-27 s.d. 30 belum berisi test case, jadi acuannya proposal Tabel 5.1).
 */
class Hafidz_PBI08_PBI11_KasusEksepsiProposalTest extends FiturTestCase
{
    /** PBI-08 Normal: tabel menampilkan kolom kategori, kesulitan, poin, emisi, status dengan filter status. */
    public function test_pbi08_normal_tabel_menampilkan_kolom_dan_filter_status(): void
    {
        $admin = $this->admin();
        $kategori = $this->kategori('Energi');
        $this->challenge($kategori, ['title' => 'Challenge Aktif Uji', 'difficulty' => 'sedang', 'points' => 30, 'estimated_co2e_gram' => 250]);
        $this->challenge($kategori, ['title' => 'Challenge Mati Uji', 'is_active' => false]);

        $this->browse(function (Browser $browser) use ($admin) {
            $this->masuk($browser, $admin);
            $browser->waitForLocation('/admin')->visit('/admin/challenges')->waitForText('Challenge Aktif Uji');
            $browser->assertSee('Energi')->assertSee('sedang')->assertSee('30 poin')->assertSee('250')
                ->assertSee('Challenge Mati Uji');
            $this->jeda($browser, 1000);

            $browser->clickLink('Aktif')->waitUntilMissingText('Challenge Mati Uji')->assertSee('Challenge Aktif Uji');
            $this->jeda($browser, 1000);
            $browser->clickLink('Nonaktif')->waitUntilMissingText('Challenge Aktif Uji')->assertSee('Challenge Mati Uji');
            $this->jeda($browser, 1000);
            $browser->clickLink('Semua')->waitForText('Challenge Aktif Uji')->assertSee('Challenge Mati Uji');
            $this->jeda($browser, 800);
        });
    }

    /** PBI-08 Eksepsi: daftar kosong menampilkan pesan "Belum ada challenge". */
    public function test_pbi08_eksepsi_daftar_kosong_menampilkan_pesan(): void
    {
        $admin = $this->admin();

        $this->browse(function (Browser $browser) use ($admin) {
            $this->masuk($browser, $admin);
            $browser->waitForLocation('/admin')->visit('/admin/challenges')->waitForText('Belum ada challenge');
            $browser->assertSee('Belum ada challenge')->assertPathIs('/admin/challenges');
            $this->jeda($browser, 1000);
        });
    }

    /** PBI-09 Normal: challenge baru yang aktif tampil di katalog mahasiswa. */
    public function test_pbi09_normal_challenge_baru_tersimpan_dan_tampil_di_katalog(): void
    {
        $admin = $this->admin();
        $mahasiswa = $this->mahasiswa();
        $kategori = $this->kategori('Energi');

        $this->browse(function (Browser $browser) use ($admin, $mahasiswa, $kategori) {
            $this->masuk($browser, $admin);
            $browser->waitForLocation('/admin')->visit('/admin/challenges/create')->waitForText('Tambah Challenge');
            $browser->select('category_id', (string) $kategori->id)->type('title', 'Cabut Charger Tak Terpakai')
                ->type('description', 'Cabut charger dari stop kontak bila tidak dipakai.')
                ->select('difficulty', 'mudah')->type('points', '15')
                ->type('proof_criteria', 'Foto stop kontak tanpa charger.')
                ->type('estimated_co2e_gram', '80')->type('emission_source', 'Estimasi konsumsi siaga charger');
            $this->jeda($browser);
            $browser->press('Simpan Challenge')->waitForLocation('/admin/challenges')->assertSee('Cabut Charger Tak Terpakai');
            $this->jeda($browser, 800);

            $browser->logout();
            $this->masuk($browser, $mahasiswa);
            $browser->waitForLocation('/dashboard')->visit('/challenges')->waitForText('Cabut Charger Tak Terpakai');
            $this->jeda($browser, 1000);
        });
    }

    /** PBI-09 Eksepsi: kriteria bukti kosong atau estimasi emisi negatif ditolak. */
    public function test_pbi09_eksepsi_kriteria_kosong_atau_emisi_negatif_ditolak(): void
    {
        $admin = $this->admin();
        $kategori = $this->kategori('Energi');

        $this->browse(function (Browser $browser) use ($admin, $kategori) {
            $this->masuk($browser, $admin);
            $browser->waitForLocation('/admin')->visit('/admin/challenges/create')->waitForText('Tambah Challenge');

            // Kriteria bukti dikosongkan.
            $isi = function (Browser $b, string $kriteria, string $emisi) use ($kategori) {
                $b->select('category_id', (string) $kategori->id)->type('title', 'Uji Validasi')
                    ->type('description', 'Deskripsi challenge untuk menguji validasi.')
                    ->select('difficulty', 'mudah')->type('points', '10')
                    ->type('estimated_co2e_gram', $emisi)->type('emission_source', 'Sumber estimasi uji');
                $b->type('proof_criteria', $kriteria);
            };
            $isi($browser, '', '100');
            $this->jeda($browser);
            $browser->press('Simpan Challenge');
            $this->jeda($browser, 1000);
            $browser->assertPathIs('/admin/challenges/create');
            $this->assertSame(0, Challenge::count());

            // Estimasi emisi negatif.
            $browser->visit('/admin/challenges/create')->waitForText('Tambah Challenge');
            $isi($browser, 'Foto bukti kegiatan.', '-50');
            $this->jeda($browser);
            $browser->press('Simpan Challenge');
            $this->jeda($browser, 1000);
            $browser->assertPathIs('/admin/challenges/create');
            $this->assertSame(0, Challenge::count());
        });
    }

    /** PBI-10 Eksepsi: poin nol atau negatif ditolak saat mengubah challenge. */
    public function test_pbi10_eksepsi_poin_nol_atau_negatif_ditolak(): void
    {
        $admin = $this->admin();
        $challenge = $this->challenge(null, ['title' => 'Judul Tetap', 'points' => 20]);

        $this->browse(function (Browser $browser) use ($admin, $challenge) {
            $this->masuk($browser, $admin);
            $browser->waitForLocation('/admin')->visit('/admin/challenges/'.$challenge->id.'/edit')->waitForText('Edit Challenge');

            foreach (['0', '-5'] as $poin) {
                $browser->clear('#points')->type('#points', $poin);
                $this->jeda($browser, 500);
                $browser->press('Simpan Perubahan');
                $this->jeda($browser, 1000);
                $browser->assertPathIs('/admin/challenges/'.$challenge->id.'/edit');
                $this->assertSame(20, (int) $challenge->fresh()->points, "Poin '$poin' seharusnya ditolak.");
            }
        });
    }

    /** PBI-11 Normal: challenge yang dinonaktifkan hilang dari katalog mahasiswa. */
    public function test_pbi11_normal_challenge_nonaktif_hilang_dari_katalog(): void
    {
        $admin = $this->admin();
        $mahasiswa = $this->mahasiswa();
        $challenge = $this->challenge(null, ['title' => 'Akan Dimatikan']);

        $this->browse(function (Browser $browser) use ($admin, $mahasiswa, $challenge) {
            $this->masuk($browser, $mahasiswa);
            $browser->waitForLocation('/dashboard')->visit('/challenges')->waitForText('Akan Dimatikan');
            $this->jeda($browser, 800);
            $browser->logout();

            $this->masuk($browser, $admin);
            $browser->waitForLocation('/admin')->visit('/admin/challenges')->waitForText('Akan Dimatikan');
            $browser->press('Nonaktifkan')->acceptDialog();
            $browser->waitForText('Aktifkan');
            $this->jeda($browser, 800);
            $browser->logout();

            $this->masuk($browser, $mahasiswa);
            $browser->waitForLocation('/dashboard')->visit('/challenges')->waitForText('challenge tersedia');
            $browser->assertDontSee('Akan Dimatikan');
            $this->jeda($browser, 1000);
        });
    }

    /** PBI-11 Eksepsi: klaim pada challenge yang baru dinonaktifkan ditolak dengan alasan "Challenge ini sudah tidak aktif". */
    public function test_pbi11_eksepsi_klaim_pada_challenge_nonaktif_ditolak(): void
    {
        $mahasiswa = $this->mahasiswa();
        $challenge = $this->challenge(null, ['title' => 'Segera Nonaktif']);

        $this->browse(function (Browser $browser) use ($mahasiswa, $challenge) {
            $this->masuk($browser, $mahasiswa);
            $browser->waitForLocation('/dashboard')->visit('/challenges/'.$challenge->id.'/claims')
                ->waitForText('Kirim bukti')
                ->attach('photo', $this->fotoPng(61));
            $this->jeda($browser, 800);

            // Admin menonaktifkan challenge saat formulir masih terbuka, lalu mahasiswa menekan Kirim.
            $challenge->update(['is_active' => false]);
            $browser->press('Kirim bukti')->waitForText('Challenge ini sudah tidak aktif.');
            $this->jeda($browser, 1000);
        });

        $this->assertSame(0, \App\Models\Claim::count());
        $this->assertSame(0, (int) $mahasiswa->fresh()->total_points);
    }
}
