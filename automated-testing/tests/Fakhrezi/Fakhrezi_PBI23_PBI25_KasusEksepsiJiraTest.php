<?php

namespace Tests\Browser\Fitur\Fakhrezi;

use Laravel\Dusk\Browser;
use Tests\Browser\Fitur\FiturTestCase;

/**
 * Fakhrezi – melengkapi test case Jira PBI-23 (TC-23-01..03), PBI-24 (TC-24-01), dan PBI-25
 * (TC-25-01, TC-25-02, TC-25-05) yang belum tercakup di Fakhrezi_PBI23_PBI25_ProfilTest.
 */
class Fakhrezi_PBI23_PBI25_KasusEksepsiJiraTest extends FiturTestCase
{
    private function perluGd(): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('Ekstensi GD belum aktif; layanan foto profil butuh GD.');
        }
    }

    /** PBI-23 TC-23-02: tamu membuka halaman profil diarahkan ke login. */
    public function test_pbi23_tc02_tamu_membuka_profil_diarahkan_ke_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/profil')->waitForText('Masuk.')->assertPathIs('/login')->assertDontSee('Edit profil');
            $this->jeda($browser, 1000);
        });
    }

    /** PBI-23 TC-23-03: mahasiswa tanpa foto melihat avatar default berisi inisial. */
    public function test_pbi23_tc03_tanpa_foto_menampilkan_avatar_inisial(): void
    {
        $user = $this->mahasiswa(['name' => 'Nadia Putri', 'username' => 'nadia_hijau', 'email' => 'nadia@ecochallenge.test']);

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/profil');
            $browser->assertSeeIn('@profile-name', 'Nadia Putri')
                ->assertSeeIn('@profile-username', '@nadia_hijau')
                ->assertSeeIn('@profile-email', 'nadia@ecochallenge.test')
                ->assertMissing('@profile-photo')
                ->assertSee('NP');
            $this->jeda($browser, 1200);
        });
    }

    /** PBI-23 TC-23-01 / PBI-25 TC-25-01: profil dengan foto menampilkan foto yang diunggah (JPG). */
    public function test_pbi23_tc01_dan_pbi25_tc01_foto_jpg_tersimpan_dan_tampil_di_profil(): void
    {
        $this->perluGd();
        $user = $this->mahasiswa();

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/profil/edit')->attach('@photo', $this->fotoJpeg(31));
            $this->jeda($browser);
            $browser->click('@save-photo')->waitForLocation('/profil')
                ->waitForText('Foto profil berhasil diperbarui.')->assertPresent('@profile-photo');
            $this->jeda($browser, 1200);
        });

        $this->assertNotNull($user->fresh()->profile_photo_path);
    }

    /** PBI-24 TC-24-01: mengubah nama, username, dan email; username baru tampil di leaderboard. */
    public function test_pbi24_tc01_ubah_nama_username_email_dan_username_baru_tampil_di_leaderboard(): void
    {
        $user = $this->mahasiswa(['name' => 'Raka Pratama', 'username' => 'raka_eco', 'email' => 'raka@ecochallenge.test', 'total_points' => 40]);

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/profil/edit')
                ->assertInputValue('@name', 'Raka Pratama')
                ->assertInputValue('@username', 'raka_eco')
                ->assertInputValue('@email', 'raka@ecochallenge.test');
            $this->jeda($browser, 600);

            $browser->type('@name', 'Raka P.')->type('@username', 'raka_hijau')->type('@email', 'raka.p@ecochallenge.test');
            $this->jeda($browser);
            $browser->click('@save-profile')->waitForLocation('/profil')
                ->assertSeeIn('@profile-status', 'Profil berhasil diperbarui.')
                ->assertSeeIn('@profile-name', 'Raka P.')
                ->assertSeeIn('@profile-username', '@raka_hijau')
                ->assertSeeIn('@profile-email', 'raka.p@ecochallenge.test');
            $this->jeda($browser, 800);

            $browser->visit('/leaderboard')->waitForText('POSISI KAMU')->assertSee('raka_hijau')->assertDontSee('raka_eco');
            $this->jeda($browser, 1200);
        });
    }

    /** PBI-25 TC-25-02: berkas PDF ditolak dan foto lama tetap dipakai. */
    public function test_pbi25_tc02_berkas_bukan_jpg_png_ditolak_dan_foto_lama_tetap(): void
    {
        $this->perluGd();
        $user = $this->mahasiswa();

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/profil/edit')->attach('@photo', $this->fotoJpeg(32));
            $browser->click('@save-photo')->waitForLocation('/profil')->waitForText('Foto profil berhasil diperbarui.');
            $fotoLama = $user->fresh()->profile_photo_path;
            $this->jeda($browser);

            $browser->visit('/profil/edit')->attach('@photo', $this->berkasPdf());
            $this->jeda($browser, 500);
            $browser->click('@save-photo')->waitForText('Foto harus berformat JPG atau PNG.');
            $this->jeda($browser, 1000);

            $this->assertSame($fotoLama, $user->fresh()->profile_photo_path, 'Foto lama harus tetap dipakai.');
            $browser->visit('/profil')->assertPresent('@profile-photo');
        });
    }

    /** PBI-25 TC-25-05: foto lebih dari 2 MB ditolak. */
    public function test_pbi25_tc05_foto_lebih_dari_2_mb_ditolak(): void
    {
        $this->perluGd();
        $user = $this->mahasiswa();

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/profil/edit')->attach('@photo', $this->fotoPngBesar());
            $this->jeda($browser, 800);
            $browser->click('@save-photo')->waitForText('maksimal 2 MB');
            $this->jeda($browser, 1000);
        });

        $this->assertNull($user->fresh()->profile_photo_path);
    }
}
