<?php

namespace Tests\Browser\Fitur\Seto;

use App\Models\Category;
use Laravel\Dusk\Browser;
use Tests\Browser\Fitur\FiturTestCase;

/** Seto – PBI-05 s.d. PBI-07: lihat, tambah, dan hapus kategori challenge oleh admin. */
class Seto_PBI05_PBI07_KelolaKategoriTest extends FiturTestCase
{
    public function test_admin_melihat_daftar_kategori_beserta_jumlah_challenge(): void
    {
        $admin = $this->admin();
        $kategori = $this->kategori('Transportasi');
        $this->challenge($kategori, ['title' => 'Berjalan Kaki ke Kampus']);

        $this->browse(function (Browser $browser) use ($admin) {
            $this->masuk($browser, $admin);
            $browser->waitForLocation('/admin')->visit('/admin/categories')->waitForText('Kelola kategori');
            $this->jeda($browser);

            $browser->assertSee('Transportasi');
            $this->jeda($browser, 1200);
        });
    }

    public function test_admin_menambah_dan_menghapus_kategori_yang_belum_dipakai(): void
    {
        $admin = $this->admin();

        $this->browse(function (Browser $browser) use ($admin) {
            $this->masuk($browser, $admin);
            $browser->waitForLocation('/admin')->visit('/admin/categories')->waitForText('Kelola kategori');
            $this->jeda($browser);

            $browser->type('name', 'Energi Surya')->type('description', 'Penggunaan energi terbarukan.');
            $this->jeda($browser, 600);
            $browser->press('Tambah Kategori')->waitForText('Energi Surya');
            $this->jeda($browser, 1200);
            $this->assertDatabaseHas('categories', ['name' => 'Energi Surya']);

            $browser->press('Hapus')->acceptDialog();
            $browser->waitUntilMissingText('Energi Surya');
            $this->jeda($browser, 1200);
            $this->assertSame(0, Category::where('name', 'Energi Surya')->count());
        });
    }

    public function test_mahasiswa_tidak_bisa_membuka_halaman_kelola_kategori(): void
    {
        $user = $this->mahasiswa();

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/admin/categories')->assertSee('403');
            $this->jeda($browser, 1000);
        });
    }
}