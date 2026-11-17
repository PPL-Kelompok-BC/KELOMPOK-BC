<?php

namespace Tests\Browser\Fitur\Jonathan;

use Tests\Browser\Fitur\FiturTestCase;

use Laravel\Dusk\Browser;

/** Jonathan – PBI-04: login, logout, pengalihan sesuai peran (termasuk dashboard admin), dan batas percobaan login. */
class Jonathan_PBI04_LoginTest extends FiturTestCase
{
    public function test_kata_sandi_salah_menampilkan_pesan_tanpa_membocorkan_penyebab(): void
    {
        $user = $this->mahasiswa();

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user, 'salah-semua');
            $browser->waitForText('Email atau kata sandi salah.')->assertPathIs('/login');
            $this->jeda($browser, 1200);
        });
    }

    public function test_mahasiswa_masuk_ke_dashboard_mahasiswa(): void
    {
        $user = $this->mahasiswa();

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->waitForText('DASHBOARD MAHASISWA')->waitForText($user->name)->assertSee('DASHBOARD MAHASISWA');
            $this->jeda($browser, 1200);
        });
    }

    public function test_admin_masuk_ke_dashboard_admin(): void
    {
        $admin = $this->admin();

        $this->browse(function (Browser $browser) use ($admin) {
            $this->masuk($browser, $admin);
            $browser->waitForLocation('/admin')->waitForText('DASHBOARD ADMIN')->assertSee('DASHBOARD ADMIN');
            $this->jeda($browser, 1200);
        });
    }

    public function test_mahasiswa_tidak_boleh_membuka_halaman_admin(): void
    {
        $user = $this->mahasiswa();

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard');
            $this->jeda($browser);

            $browser->visit('/admin')->assertSee('403');
            $this->jeda($browser, 1200);
        });
    }

    public function test_akun_nonaktif_tidak_bisa_masuk(): void
    {
        $user = $this->mahasiswa(['is_active' => false]);

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user);
            $this->jeda($browser, 1000);
            $browser->assertPathIs('/login');
        });
    }

    public function test_keluar_mengakhiri_sesi_dan_dashboard_kembali_meminta_login(): void
    {
        $user = $this->mahasiswa();

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard');
            $this->jeda($browser);

            $this->keluar($browser);
            $browser->waitForLocation('/login');
            $this->jeda($browser);

            $browser->visit('/dashboard')->assertPathIs('/login');
            $this->jeda($browser, 1000);
        });
    }

    public function test_setelah_lima_kali_gagal_login_ditahan_dengan_hitung_mundur(): void
    {
        $user = $this->mahasiswa(['email' => 'throttle.'.uniqid().'@student.telkomuniversity.ac.id']);

        $this->browse(function (Browser $browser) use ($user) {
            for ($i = 1; $i <= 6; $i++) {
                $browser->visit('/login')
                    ->type('email', $user->email)
                    ->type('password', 'salah-'.$i)
                    ->press('Masuk');
                $browser->waitForText($i < 6 ? 'Email atau kata sandi salah.' : 'detik');
                $this->jeda($browser, 250);
            }

            $browser->assertSee('Coba lagi dalam');
            $this->jeda($browser, 1500);
        });
    }

    public function test_dashboard_admin_menampilkan_ringkasan_dan_pintasan(): void
    {
        $admin = $this->admin();
        $this->mahasiswa();

        $this->browse(function (Browser $browser) use ($admin) {
            $this->masuk($browser, $admin);
            $browser->waitForLocation('/admin')
                ->waitForText('MAHASISWA AKTIF')
                ->waitForText('KLAIM MENUNGGU')
                ->waitForText('Kelola challenge');
            $this->jeda($browser, 1200);

            $browser->clickLink('Kelola challenge')->waitForLocation('/admin/challenges')->assertSee('Kelola Challenge');
            $this->jeda($browser, 1000);
        });
    }
}
