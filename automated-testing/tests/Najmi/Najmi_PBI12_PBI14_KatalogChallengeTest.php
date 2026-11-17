<?php

namespace Tests\Browser\Fitur\Najmi;

use Tests\Browser\Fitur\FiturTestCase;

use Laravel\Dusk\Browser;

/** Najmi – PBI-12 s.d. PBI-14: daftar challenge aktif, filter kategori, dan detail. */
class Najmi_PBI12_PBI14_KatalogChallengeTest extends FiturTestCase
{
    public function test_katalog_hanya_menampilkan_challenge_aktif(): void
    {
        $user = $this->mahasiswa();
        $energi = $this->kategori('Energi');
        $this->challenge($energi, ['title' => 'Matikan Lampu']);
        $this->challenge($energi, ['title' => 'Cabut Charger']);
        $this->challenge($energi, ['title' => 'Challenge Tersembunyi', 'is_active' => false]);

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->clickLink('Challenge')->waitForLocation('/challenges');
            $this->jeda($browser);

            $browser->assertSee('2 challenge tersedia')
                ->assertSee('Matikan Lampu')
                ->assertSee('Cabut Charger')
                ->assertDontSee('Challenge Tersembunyi');
            $this->jeda($browser, 1000);
        });
    }

    public function test_filter_kategori_menyaring_dan_semua_mengembalikan_daftar(): void
    {
        $user = $this->mahasiswa();
        $this->challenge($this->kategori('Energi'), ['title' => 'Matikan Lampu']);
        $this->challenge($this->kategori('Sampah'), ['title' => 'Pilah Sampah']);

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/challenges')->assertSee('2 challenge tersedia');
            $this->jeda($browser);

            $browser->clickLink('Energi')->waitForText('1 challenge tersedia')
                ->assertSee('Matikan Lampu')->assertDontSee('Pilah Sampah');
            $this->jeda($browser, 1000);

            $browser->clickLink('Sampah')->waitForText('Pilah Sampah')->assertDontSee('Matikan Lampu');
            $this->jeda($browser, 1000);

            $browser->clickLink('Semua')->waitForText('2 challenge tersedia');
            $this->jeda($browser, 800);
        });
    }

    public function test_detail_challenge_menampilkan_kriteria_bukti_dan_tombol_kirim_bukti(): void
    {
        $user = $this->mahasiswa();
        $this->challenge(null, [
            'title' => 'Bawa Botol Minum Sendiri',
            'proof_criteria' => 'Foto botol minum isi ulang di meja kamu.',
            'points' => 15,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/challenges');
            $this->jeda($browser);

            $browser->clickLink('Lihat detail')->assertSee('Bawa Botol Minum Sendiri')
                ->assertSee('Foto botol minum isi ulang di meja kamu.')
                ->assertSee('15');
            $this->jeda($browser, 1000);

            $browser->clickLink('Kirim bukti')->assertSee('Kirim bukti')->assertPresent('input[name="photo"]');
            $this->jeda($browser, 1000);
        });
    }

    public function test_challenge_nonaktif_tidak_bisa_dibuka_lewat_alamatnya(): void
    {
        $user = $this->mahasiswa();
        $mati = $this->challenge(null, ['title' => 'Sudah Dimatikan', 'is_active' => false]);

        $this->browse(function (Browser $browser) use ($user, $mati) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/challenges/'.$mati->id)->assertSee('404');
            $this->jeda($browser, 1000);
        });
    }
}
