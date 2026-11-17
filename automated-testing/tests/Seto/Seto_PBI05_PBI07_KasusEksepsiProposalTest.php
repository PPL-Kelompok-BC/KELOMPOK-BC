<?php

namespace Tests\Browser\Fitur\Seto;

use App\Models\Category;
use Laravel\Dusk\Browser;
use Tests\Browser\Fitur\FiturTestCase;

/**
 * Seto – melengkapi kriteria penerimaan proposal untuk PBI-05 s.d. PBI-07
 * (kartu Jira SCRUM-10, 25, 26 belum berisi test case, jadi acuannya proposal Tabel 5.1).
 */
class Seto_PBI05_PBI07_KasusEksepsiProposalTest extends FiturTestCase
{
    /** PBI-05 Normal: halaman menampilkan nama dan jumlah challenge tiap kategori. */
    public function test_pbi05_normal_nama_dan_jumlah_challenge_tiap_kategori(): void
    {
        $admin = $this->admin();
        $energi = $this->kategori('Energi');
        $this->kategori('Sampah');
        $this->challenge($energi, ['title' => 'Satu']);
        $this->challenge($energi, ['title' => 'Dua']);
        $this->challenge($energi, ['title' => 'Tiga']);

        $this->browse(function (Browser $browser) use ($admin) {
            $this->masuk($browser, $admin);
            $browser->waitForLocation('/admin')->visit('/admin/categories')->waitForText('Kelola kategori');
            $baris = $browser->script('return [...document.querySelectorAll("tbody tr")].map(r => r.innerText.replace(/\s+/g, " ").trim());');
            $teks = implode(' | ', $baris[0]);
            $this->assertMatchesRegularExpression('/Energi.*\b3\b/', $teks);
            $this->assertMatchesRegularExpression('/Sampah.*\b0\b/', $teks);
            $this->jeda($browser, 1200);
        });
    }

    /** PBI-06 Eksepsi: nama kategori yang sudah ada atau kosong ditolak dengan pesan. */
    public function test_pbi06_eksepsi_nama_kategori_sama_atau_kosong_ditolak(): void
    {
        $admin = $this->admin();
        $this->kategori('Transportasi');

        $this->browse(function (Browser $browser) use ($admin) {
            $this->masuk($browser, $admin);
            $browser->waitForLocation('/admin')->visit('/admin/categories')->waitForText('Kelola kategori');

            $browser->type('name', 'Transportasi');
            $this->jeda($browser, 600);
            $browser->press('Tambah Kategori')->waitForText('Nama kategori sudah terdaftar.');
            $this->jeda($browser, 1000);

            $browser->visit('/admin/categories')->press('Tambah Kategori')->waitForText('Nama kategori wajib diisi.');
            $this->jeda($browser, 1000);
        });

        $this->assertSame(1, Category::where('name', 'Transportasi')->count());
    }

    /** PBI-07 Normal: perubahan nama kategori tampil di daftar dan di katalog mahasiswa. */
    public function test_pbi07_normal_ubah_nama_kategori_tampil_di_daftar_dan_katalog(): void
    {
        $admin = $this->admin();
        $mahasiswa = $this->mahasiswa();
        $kategori = $this->kategori('Energi Lama');
        $this->challenge($kategori, ['title' => 'Matikan Lampu']);

        $this->browse(function (Browser $browser) use ($admin, $mahasiswa) {
            $this->masuk($browser, $admin);
            $browser->waitForLocation('/admin')->visit('/admin/categories')->waitForText('Energi Lama');
            $this->jeda($browser);

            $browser->press('Ubah')->waitFor('#edit_name');
            $this->jeda($browser, 600);
            $browser->clear('#edit_name')->type('#edit_name', 'Energi Baru');
            $this->jeda($browser, 600);
            $browser->press('Simpan Perubahan')->waitForText('Energi Baru');
            $this->jeda($browser, 1000);
            // Pesan sukses bisa menyebut nama lama, jadi cek barisnya di basis data, bukan di seluruh halaman.
            $this->assertSame(1, Category::where('name', 'Energi Baru')->count());
            $this->assertSame(0, Category::where('name', 'Energi Lama')->count());
            $browser->logout();

            $this->masuk($browser, $mahasiswa);
            $browser->waitForLocation('/dashboard')->visit('/challenges')->waitForText('Energi Baru');
            $browser->assertDontSee('Energi Lama');
            $this->jeda($browser, 1000);
        });
    }

    /** PBI-07 Eksepsi: kategori yang masih dipakai challenge tidak bisa dihapus. */
    public function test_pbi07_eksepsi_kategori_yang_dipakai_challenge_tidak_bisa_dihapus(): void
    {
        $admin = $this->admin();
        $kategori = $this->kategori('Dipakai');
        $this->challenge($kategori, ['title' => 'Memakai Kategori']);

        $this->browse(function (Browser $browser) use ($admin) {
            $this->masuk($browser, $admin);
            $browser->waitForLocation('/admin')->visit('/admin/categories')->waitForText('Dipakai');
            $this->jeda($browser, 800);

            $browser->assertPresent('button[disabled][title*="tidak dapat dihapus"]')
                ->assertSee('Kategori yang masih punya challenge tidak bisa dihapus.');
            $this->jeda($browser, 1200);
        });

        $this->assertSame(1, Category::where('name', 'Dipakai')->count());
    }
}
