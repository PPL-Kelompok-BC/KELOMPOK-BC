<?php

namespace Tests\Browser\Fitur\Najmi;

use Laravel\Dusk\Browser;
use Tests\Browser\Fitur\FiturTestCase;

/** Najmi – layout dasar (SCRUM-13) dan dashboard mahasiswa: navbar, ringkasan, serta menu di layar HP. */
class Najmi_Layout_DashboardNavigasiTest extends FiturTestCase
{
    public function test_dashboard_mahasiswa_menampilkan_ringkasan_dan_tombol_pilih_challenge(): void
    {
        $user = $this->mahasiswa(['total_points' => 120]);
        $this->challenge(null, ['title' => 'Matikan Lampu Saat Tidak Dipakai']);

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')
                ->waitForText('TOTAL POIN')
                ->assertSee('120')
                ->assertSee('PERINGKAT')
                ->assertSee('Matikan Lampu Saat Tidak Dipakai');
            $this->jeda($browser, 1200);

            $browser->clickLink('Pilih challenge')->waitForLocation('/challenges')->assertSee('Temukan challenge-mu.');
            $this->jeda($browser, 1000);
        });
    }

    public function test_menu_navbar_berpindah_antar_halaman_utama(): void
    {
        $user = $this->mahasiswa();

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard');

            foreach ([
                ['Challenge', '/challenges', 'Temukan challenge-mu.'],
                ['Klaim', '/claims', 'Klaim saya'],
                ['Leaderboard', '/leaderboard', 'Papan aksi baik.'],
                ['Dashboard', '/dashboard', 'DASHBOARD MAHASISWA'],
            ] as [$menu, $path, $teks]) {
                $this->jeda($browser, 600);
                $browser->click('header nav a[href$="'.$path.'"]')->waitForLocation($path)->assertSee($teks);
            }
            $this->jeda($browser, 1000);
        });
    }

    public function test_menu_akun_membuka_profil_saya(): void
    {
        $user = $this->mahasiswa();

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->click('header button[aria-haspopup="menu"]');
            $this->jeda($browser);

            $browser->assertSee('Profil saya')->clickLink('Profil saya')->waitForLocation('/profil')->waitForText($user->name)->assertSee($user->name);
            $this->jeda($browser, 1000);
        });
    }

    public function test_tampilan_hp_memakai_menu_geser_dan_tanpa_geser_horizontal(): void
    {
        $user = $this->mahasiswa();

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->resize(390, 844);
            $this->jeda($browser, 1000);

            $browser->assertMissing('#menu-ponsel');
            $browser->click('button[aria-label="Buka menu navigasi"]')->waitFor('#menu-ponsel');
            $this->jeda($browser, 1000);

            $browser->within('#menu-ponsel', fn (Browser $menu) => $menu->clickLink('Leaderboard'));
            $browser->waitForLocation('/leaderboard')->assertSee('Papan aksi baik.');

            // Tidak ada elemen yang membuat halaman bisa digeser ke samping.
            $lebih = $browser->script('return document.documentElement.scrollWidth - document.documentElement.clientWidth;');
            $this->assertLessThanOrEqual(1, (int) $lebih[0]);
            $this->jeda($browser, 1000);
        });
    }
}
