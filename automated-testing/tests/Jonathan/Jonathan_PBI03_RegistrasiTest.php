<?php

namespace Tests\Browser\Fitur\Jonathan;

use Tests\Browser\Fitur\FiturTestCase;

use Laravel\Dusk\Browser;

/** Jonathan – PBI-03: registrasi akun mahasiswa. */
class Jonathan_PBI03_RegistrasiTest extends FiturTestCase
{
    public function test_formulir_kosong_menampilkan_pesan_kesalahan_per_kolom(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')->waitForText('Buat akunmu.');
            $this->jeda($browser);

            $browser->press('Daftar')->waitFor('#username-error');
            $this->jeda($browser);

            $browser->assertPresent('#name-error')
                ->assertPresent('#username-error')
                ->assertPresent('#email-error')
                ->assertPresent('#password-error');
            $this->jeda($browser, 1200);
        });
    }

    public function test_pratinjau_username_dan_penguat_kata_sandi_bereaksi_saat_mengetik(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register');

            $browser->type('username', 'Rani.Hijau');
            $this->jeda($browser);
            $browser->assertSee('@rani.hijau');

            $browser->type('password', 'abc');
            $this->jeda($browser);
            $browser->assertSee('Lemah');

            $browser->type('password', 'Abc12345!x');
            $this->jeda($browser);
            $browser->assertSee('Baik');

            $browser->type('password_confirmation', 'beda');
            $this->jeda($browser);
            $browser->assertSee('Kata sandi belum sama');

            $browser->type('password_confirmation', 'Abc12345!x');
            $this->jeda($browser);
            $browser->assertSee('Kata sandi cocok');
            $this->jeda($browser, 1000);
        });
    }

    public function test_tombol_lihat_sandi_mengubah_tipe_kolom(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')->type('password', 'rahasia123');
            $this->jeda($browser);
            $browser->assertAttribute('#password', 'type', 'password');

            $browser->click('button[aria-label="Tampilkan kata sandi"]');
            $this->jeda($browser);
            $browser->assertAttribute('#password', 'type', 'text');
        });
    }

    public function test_registrasi_berhasil_lalu_masuk_ke_dashboard_mahasiswa(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register');
            $this->jeda($browser, 400);

            $browser->type('name', 'Rani Hijau');
            $this->jeda($browser, 400);
            $browser->type('username', 'rani.hijau');
            $this->jeda($browser, 400);
            $browser->type('email', 'rani@student.telkomuniversity.ac.id');
            $this->jeda($browser, 400);
            $browser->type('password', self::KATA_SANDI);
            $this->jeda($browser, 400);
            $browser->type('password_confirmation', self::KATA_SANDI);
            $this->jeda($browser);

            $browser->press('Daftar')
                ->waitForLocation('/dashboard')
                ->waitForText('DASHBOARD MAHASISWA')
                ->waitForText('Rani Hijau')
                ->assertSee('DASHBOARD MAHASISWA');
            $this->jeda($browser, 1500);
        });

        $this->assertDatabaseHas('users', ['username' => 'rani.hijau', 'role' => 'mahasiswa', 'is_active' => 1]);
    }

    public function test_username_dan_email_yang_sudah_dipakai_ditolak(): void
    {
        $ada = $this->mahasiswa(['username' => 'sudah.ada', 'email' => 'sudah@ada.test']);

        $this->browse(function (Browser $browser) use ($ada) {
            $browser->visit('/register')
                ->type('name', 'Orang Lain')
                ->type('username', $ada->username)
                ->type('email', $ada->email)
                ->type('password', self::KATA_SANDI)
                ->type('password_confirmation', self::KATA_SANDI);
            $this->jeda($browser);

            $browser->press('Daftar')->waitFor('#username-error');
            $this->jeda($browser);

            $browser->assertSee('Username sudah dipakai. Pilih username lain.')
                ->assertSee('Email sudah terdaftar. Gunakan email lain atau masuk.');
            $this->jeda($browser, 1200);
        });
    }

    public function test_username_dengan_huruf_besar_atau_spasi_ditolak(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->type('name', 'Rani')
                ->type('username', 'Rani Hijau!')
                ->type('email', 'rani.baru@student.telkomuniversity.ac.id')
                ->type('password', self::KATA_SANDI)
                ->type('password_confirmation', self::KATA_SANDI);
            $this->jeda($browser);

            $browser->press('Daftar')->waitFor('#username-error');
            $browser->assertSee('Username hanya boleh berisi huruf kecil, angka, titik, dan garis bawah.');
            $this->jeda($browser, 1200);
        });
    }
}
