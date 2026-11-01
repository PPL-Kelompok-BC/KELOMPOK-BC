<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class HomePageTest extends DuskTestCase
{
    public function test_beranda_terbuka_tanpa_error(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertTitleContains('EcoChallenge')
                ->waitForText('Temukan challenge', 10);
        });
    }

    public function test_url_tidak_terdaftar_menampilkan_404(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/halaman-tidak-ada')
                ->assertTitleContains('404')
                ->waitForText('Halaman yang kamu cari tidak ditemukan.', 10);
        });
    }
}