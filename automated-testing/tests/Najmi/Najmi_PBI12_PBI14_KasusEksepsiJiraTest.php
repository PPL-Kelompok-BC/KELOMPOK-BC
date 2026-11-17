<?php

namespace Tests\Browser\Fitur\Najmi;

use Laravel\Dusk\Browser;
use Tests\Browser\Fitur\FiturTestCase;

/**
 * Najmi – melengkapi test case Jira PBI-12 (TC-01..04), PBI-13 (TC-01..04), dan PBI-14 (TC-01..04)
 * yang belum tercakup di Najmi_PBI12_PBI14_KatalogChallengeTest.
 */
class Najmi_PBI12_PBI14_KasusEksepsiJiraTest extends FiturTestCase
{
    /** PBI-12 TC-01: kartu memuat kategori, kesulitan, poin, dan estimasi emisi. */
    public function test_pbi12_tc01_kartu_memuat_kategori_kesulitan_poin_dan_estimasi_emisi(): void
    {
        $user = $this->mahasiswa();
        $this->challenge($this->kategori('Energi'), [
            'title' => 'Matikan Lampu', 'difficulty' => 'sedang', 'points' => 35, 'estimated_co2e_gram' => 750,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/challenges')->waitForText('Matikan Lampu');
            $browser->assertSee('Energi')->assertSee('sedang')->assertSee('+35 poin')->assertSee('750');
            $this->jeda($browser, 1200);
        });
    }

    /** PBI-12 TC-03: belum ada challenge aktif menampilkan pesan tanpa error. */
    public function test_pbi12_tc03_belum_ada_challenge_aktif_menampilkan_pesan(): void
    {
        $user = $this->mahasiswa();
        $this->challenge(null, ['title' => 'Semua Nonaktif', 'is_active' => false]);

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/challenges')->waitForText('Belum ada challenge');
            $browser->assertSee('0 challenge tersedia')->assertDontSee('Semua Nonaktif');
            $this->jeda($browser, 1200);
        });
    }

    /** PBI-12 TC-04: tamu yang membuka katalog diarahkan ke login. */
    public function test_pbi12_tc04_tamu_membuka_katalog_diarahkan_ke_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/challenges')->waitForText('Masuk.')->assertPathIs('/login');
            $this->jeda($browser, 800);
        });
    }

    /** PBI-13 TC-02: kategori tanpa challenge aktif menampilkan pesan dan tombol lihat semua. */
    public function test_pbi13_tc02_kategori_tanpa_challenge_menampilkan_pesan_kosong(): void
    {
        $user = $this->mahasiswa();
        $this->challenge($this->kategori('Energi'), ['title' => 'Matikan Lampu']);
        $this->kategori('Sampah'); // kategori tanpa challenge aktif

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/challenges')->waitForText('Matikan Lampu');
            $browser->clickLink('Sampah')->waitForText('Belum ada challenge di kategori ini');
            $browser->assertSee('Lihat semua challenge');
            $this->jeda($browser, 1000);

            $browser->clickLink('Lihat semua challenge')->waitForText('Matikan Lampu');
            $this->jeda($browser, 800);
        });
    }

    /** PBI-13 TC-04: nilai kategori yang bukan angka diabaikan dan seluruh daftar tampil. */
    public function test_pbi13_tc04_nilai_kategori_tidak_valid_menampilkan_seluruh_daftar(): void
    {
        $user = $this->mahasiswa();
        $this->challenge($this->kategori('Energi'), ['title' => 'Matikan Lampu']);
        $this->challenge($this->kategori('Sampah'), ['title' => 'Pilah Sampah']);

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/challenges?kategori=abc')->waitForText('2 challenge tersedia');
            $browser->assertSee('Matikan Lampu')->assertSee('Pilah Sampah');
            $this->jeda($browser, 1000);
        });
    }

    /** PBI-14 TC-01: detail memuat deskripsi, kesulitan, poin, estimasi emisi beserta sumbernya, kriteria bukti. */
    public function test_pbi14_tc01_detail_memuat_semua_informasi_challenge(): void
    {
        $user = $this->mahasiswa();
        $challenge = $this->challenge($this->kategori('Energi'), [
            'title' => 'Matikan Lampu',
            'description' => 'Matikan lampu saat meninggalkan ruangan.',
            'proof_criteria' => 'Foto saklar dalam keadaan mati.',
            'emission_source' => 'Sumber: estimasi daya lampu 10 watt',
            'points' => 25, 'estimated_co2e_gram' => 150,
        ]);

        $this->browse(function (Browser $browser) use ($user, $challenge) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/challenges/'.$challenge->id)->waitForText('Matikan Lampu');
            $browser->assertSee('Energi')->assertSee('Matikan lampu saat meninggalkan ruangan.')
                ->assertSee('Foto saklar dalam keadaan mati.')->assertSee('Sumber: estimasi daya lampu 10 watt')
                ->assertSee('25')->assertSee('150');
            $this->jeda($browser, 1200);
        });
    }

    /** PBI-14 TC-03: challenge yang tidak ada menghasilkan halaman 404. */
    public function test_pbi14_tc03_challenge_tidak_ada_menghasilkan_404(): void
    {
        $user = $this->mahasiswa();

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/challenges/99999')->assertSee('404');
            $this->jeda($browser, 1000);
        });
    }

    /** PBI-14 TC-04: kriteria bukti kosong menampilkan pesan pengganti. */
    public function test_pbi14_tc04_kriteria_bukti_kosong_menampilkan_pesan_pengganti(): void
    {
        $user = $this->mahasiswa();
        $challenge = $this->challenge(null, ['title' => 'Tanpa Kriteria', 'proof_criteria' => '']);

        $this->browse(function (Browser $browser) use ($user, $challenge) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/challenges/'.$challenge->id)
                ->waitForText('Kriteria bukti untuk challenge ini belum diisi.');
            $browser->assertSee('Tanpa Kriteria');
            $this->jeda($browser, 1000);
        });
    }
}
