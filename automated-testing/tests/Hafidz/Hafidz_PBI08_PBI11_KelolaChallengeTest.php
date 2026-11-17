<?php

namespace Tests\Browser\Fitur\Hafidz;

use App\Models\Challenge;
use Laravel\Dusk\Browser;
use Tests\Browser\Fitur\FiturTestCase;

/** Hafidz – PBI-08 s.d. PBI-11: daftar, tambah, ubah, dan aktif/nonaktif challenge oleh admin. */
class Hafidz_PBI08_PBI11_KelolaChallengeTest extends FiturTestCase
{
    public function test_admin_menambah_challenge_lewat_formulir_lalu_tampil_di_daftar(): void
    {
        $admin = $this->admin();
        $kategori = $this->kategori('Energi');

        $this->browse(function (Browser $browser) use ($admin, $kategori) {
            $this->masuk($browser, $admin);
            $browser->waitForLocation('/admin')->visit('/admin/challenges')->assertSee('Kelola Challenge');
            $this->jeda($browser);

            $browser->clickLink('Tambah Challenge')->waitForLocation('/admin/challenges/create');
            $this->jeda($browser, 500);

            $browser->select('category_id', (string) $kategori->id);
            $browser->type('title', 'Matikan Lampu Saat Tidak Dipakai');
            $this->jeda($browser, 400);
            $browser->type('description', 'Matikan lampu ruangan ketika kamu meninggalkannya lebih dari lima menit.');
            $browser->select('difficulty', 'mudah');
            $this->jeda($browser, 400);
            $browser->type('points', '25');
            $browser->type('proof_criteria', 'Foto saklar lampu dalam keadaan mati.');
            $browser->type('estimated_co2e_gram', '150');
            $browser->type('emission_source', 'Estimasi dari daya lampu dan lama menyala yang dihemat');
            $this->jeda($browser);

            $browser->press('Simpan Challenge')
                ->waitForLocation('/admin/challenges')
                ->assertSee('Matikan Lampu Saat Tidak Dipakai')
                ->assertSee('25 poin');
            $this->jeda($browser, 1500);
        });

        $this->assertDatabaseHas('challenges', ['title' => 'Matikan Lampu Saat Tidak Dipakai', 'points' => 25]);
    }

    public function test_formulir_challenge_kosong_menampilkan_validasi(): void
    {
        $admin = $this->admin();
        $this->kategori('Energi');

        $this->browse(function (Browser $browser) use ($admin) {
            $this->masuk($browser, $admin);
            $browser->waitForLocation('/admin')->visit('/admin/challenges/create');
            $this->jeda($browser);

            $browser->press('Simpan Challenge');
            $this->jeda($browser, 1000);
            $browser->assertPathIs('/admin/challenges/create');
            $this->assertSame(0, Challenge::count());
        });
    }

    public function test_admin_menonaktifkan_lalu_mengaktifkan_kembali_challenge(): void
    {
        $admin = $this->admin();
        $this->challenge(null, ['title' => 'Naik Sepeda ke Kampus']);

        $this->browse(function (Browser $browser) use ($admin) {
            $this->masuk($browser, $admin);
            $browser->waitForLocation('/admin')->visit('/admin/challenges')->assertSee('Naik Sepeda ke Kampus');
            $this->jeda($browser, 1000);

            $browser->press('Nonaktifkan')->acceptDialog();
            $browser->waitForText('Aktifkan');
            $this->jeda($browser, 1200);
            $this->assertFalse((bool) Challenge::first()->is_active);

            $browser->press('Aktifkan')->acceptDialog();
            $browser->waitUntilMissingText('Aktifkan');
            $this->jeda($browser, 1200);
            $this->assertTrue((bool) Challenge::first()->is_active);
        });
    }

    public function test_admin_mengubah_challenge_lewat_halaman_edit(): void
    {
        $admin = $this->admin();
        $challenge = $this->challenge(null, ['title' => 'Judul Lama', 'points' => 20]);

        $this->browse(function (Browser $browser) use ($admin, $challenge) {
            $this->masuk($browser, $admin);
            $browser->waitForLocation('/admin')->visit('/admin/challenges')->clickLink('Edit');
            $this->jeda($browser, 600);

            $browser->assertInputValue('title', 'Judul Lama')
                ->clear('#title')->type('#title', 'Judul Baru Diubah');
            $this->jeda($browser, 400);
            $browser->clear('#points')->type('#points', '45');
            $this->jeda($browser);

            $browser->press('Simpan Perubahan')->waitForLocation('/admin/challenges')
                ->assertSee('Judul Baru Diubah')->assertSee('45 poin');
            $this->jeda($browser, 1500);
        });
    }

    public function test_mahasiswa_tidak_bisa_membuka_halaman_kelola_challenge(): void
    {
        $user = $this->mahasiswa();

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/admin/challenges')->assertSee('403');
            $this->jeda($browser, 1000);
        });
    }
}
