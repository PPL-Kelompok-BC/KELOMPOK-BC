<?php

namespace Tests\Browser\Fitur\Fakhrezi;

use Tests\Browser\Fitur\FiturTestCase;

use Laravel\Dusk\Browser;

/** Fakhrezi – PBI-23 s.d. PBI-25: lihat profil, ubah nama/username/email, dan foto profil. */
class Fakhrezi_PBI23_PBI25_ProfilTest extends FiturTestCase
{
    public function test_profil_menampilkan_data_diri_dan_tombol_edit(): void
    {
        $user = $this->mahasiswa(['name' => 'Rani Hijau', 'username' => 'rani.hijau']);

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/profil');
            $this->jeda($browser, 800);

            $browser->assertSeeIn('@profile-name', 'Rani Hijau')
                ->assertSeeIn('@profile-username', '@rani.hijau')
                ->assertSeeIn('@profile-email', $user->email);
            $this->jeda($browser, 1000);

            $browser->click('@edit-profile')->waitForLocation('/profil/edit');
            $this->jeda($browser, 800);
        });
    }

    public function test_mengubah_nama_dan_username_berhasil_dan_tampil_di_profil(): void
    {
        $user = $this->mahasiswa();

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/profil/edit');
            $this->jeda($browser, 600);

            $browser->type('@name', 'Rani Hijau Baru');
            $this->jeda($browser, 400);
            $browser->type('@username', 'rani.baru');
            $this->jeda($browser);

            $browser->click('@save-profile')->waitForLocation('/profil')
                ->assertSeeIn('@profile-status', 'Profil berhasil diperbarui.')
                ->assertSeeIn('@profile-name', 'Rani Hijau Baru')
                ->assertSeeIn('@profile-username', '@rani.baru');
            $this->jeda($browser, 1500);
        });
    }

    public function test_username_yang_sudah_dipakai_orang_lain_ditolak(): void
    {
        $lain = $this->mahasiswa(['username' => 'sudah.dipakai']);
        $user = $this->mahasiswa();

        $this->browse(function (Browser $browser) use ($user, $lain) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/profil/edit')->type('@username', $lain->username);
            $this->jeda($browser);

            $browser->click('@save-profile')->waitForText('Username sudah dipakai. Pilih username lain.')->assertSee('Username sudah dipakai. Pilih username lain.');
            $this->jeda($browser, 1500);
        });
    }

    public function test_mengunggah_foto_profil_tampil_di_halaman_profil(): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('Ekstensi GD belum aktif; layanan foto profil butuh GD untuk mengompres gambar.');
        }

        $user = $this->mahasiswa();

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/profil/edit')->attach('@photo', $this->fotoPng(21));
            $this->jeda($browser);

            $browser->click('@save-photo')->waitForLocation('/profil')
                ->assertSeeIn('@profile-status', 'Foto profil berhasil diperbarui.')
                ->assertPresent('@profile-photo');
            $this->jeda($browser, 1500);
        });
    }
}
