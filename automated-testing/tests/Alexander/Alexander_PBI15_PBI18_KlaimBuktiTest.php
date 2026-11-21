<?php

namespace Tests\Browser\Fitur\Alexander;

use Tests\Browser\Fitur\FiturTestCase;

use App\Models\Claim;
use Laravel\Dusk\Browser;

/** Alexander – PBI-15 s.d. PBI-18: unggah bukti, verifikasi berlapis, riwayat klaim, dan kirim ulang. */
class Alexander_PBI15_PBI18_KlaimBuktiTest extends FiturTestCase
{
    public function test_mahasiswa_mengunggah_foto_bukti_dan_klaim_langsung_diterima(): void
    {
        $user = $this->mahasiswa();
        $challenge = $this->challenge(null, ['title' => 'Pisahkan Sampah']);

        $this->browse(function (Browser $browser) use ($user, $challenge) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/challenges/'.$challenge->id);
            $this->jeda($browser);

            $browser->clickLink('Kirim bukti');
            $this->jeda($browser, 500);
            $browser->attach('photo', $this->fotoPng(1));
            $this->jeda($browser, 500);
            $browser->type('note', 'Memilah sampah organik dan anorganik di kos.');
            $this->jeda($browser);

            $browser->press('Kirim bukti')
                ->waitForText('Diterima')
                ->assertSee('Pisahkan Sampah');
            $this->jeda($browser, 1500);
        });

        $this->assertDatabaseHas('claims', ['user_id' => $user->id, 'challenge_id' => $challenge->id, 'status' => 'approved']);
    }

    public function test_klaim_kedua_untuk_challenge_yang_sama_di_hari_yang_sama_ditolak(): void
    {
        $user = $this->mahasiswa();
        $challenge = $this->challenge();

        $this->browse(function (Browser $browser) use ($user, $challenge) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard');

            $browser->visit('/challenges/'.$challenge->id.'/claims')
                ->attach('photo', $this->fotoPng(2))->press('Kirim bukti')
                ->waitForText('Diterima');
            $this->jeda($browser);

            $browser->visit('/challenges/'.$challenge->id.'/claims')
                ->attach('photo', $this->fotoPng(3))->press('Kirim bukti')
                ->waitForText('Kamu sudah mengklaim challenge ini hari ini.');  // proposal Tabel 2.3 butir (c)
            $this->jeda($browser, 1500);
        });

        $this->assertSame(1, Claim::where('user_id', $user->id)->count());
        $this->assertSame($challenge->points, $user->fresh()->total_points, 'Poin hanya bertambah satu kali.');
    }

    public function test_foto_yang_sama_tidak_bisa_dipakai_dua_kali(): void
    {
        $user = $this->mahasiswa();
        $a = $this->challenge(null, ['title' => 'Challenge A']);
        $b = $this->challenge(null, ['title' => 'Challenge B']);

        $this->browse(function (Browser $browser) use ($user, $a, $b) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard');

            $browser->visit('/challenges/'.$a->id.'/claims')
                ->attach('photo', $this->fotoPng(4))->press('Kirim bukti')
                ->waitForText('Diterima');
            $this->jeda($browser);

            $browser->visit('/challenges/'.$b->id.'/claims')
                ->attach('photo', $this->fotoPng(4))->press('Kirim bukti')
                ->waitForText('Foto ini sudah pernah dipakai untuk klaim lain.')
                ->assertSee('Foto ini sudah pernah dipakai untuk klaim lain.');
            $this->jeda($browser, 1500);
        });
    }

    public function test_berkas_selain_jpg_png_ditolak(): void
    {
        $user = $this->mahasiswa();
        $challenge = $this->challenge();

        $this->browse(function (Browser $browser) use ($user, $challenge) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/challenges/'.$challenge->id.'/claims')
                ->attach('photo', $this->berkasTeks());
            $this->jeda($browser);

            $browser->press('Kirim bukti')->waitForText('Foto bukti harus berformat JPG atau PNG.')->assertSee('Foto bukti harus berformat JPG atau PNG.');
            $this->jeda($browser, 1500);
        });
    }

    public function test_riwayat_klaim_menampilkan_klaim_milik_sendiri(): void
    {
        $user = $this->mahasiswa();
        $challenge = $this->challenge(null, ['title' => 'Naik Angkot']);

        $this->browse(function (Browser $browser) use ($user, $challenge) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/challenges/'.$challenge->id.'/claims')
                ->attach('photo', $this->fotoPng(5))->press('Kirim bukti')->waitForText('Diterima');
            $this->jeda($browser);

            $browser->visit('/claims')->assertSee('Klaim saya')->assertSee('Naik Angkot')->assertSee('Diterima');
            $this->jeda($browser, 1500);
        });
    }

    public function test_klaim_ditolak_menampilkan_alasan_dan_bisa_dikirim_ulang(): void
    {
        $user = $this->mahasiswa();
        $challenge = $this->challenge(null, ['title' => 'Hemat Listrik']);
        $klaim = Claim::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'proof_path' => 'claims/tidak-ada.png',
            'photo_hash' => hash('sha256', 'ditolak-uji'),
            'needs_review' => false,
            'claim_date' => today()->toDateString(),
            'status' => Claim::STATUS_REJECTED,
            'cancel_reason' => 'Foto kurang jelas, ulangi dengan cahaya cukup.',
        ]);

        $this->browse(function (Browser $browser) use ($user, $klaim) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/claims/'.$klaim->id);
            $this->jeda($browser);

            $browser->assertSee('Foto kurang jelas, ulangi dengan cahaya cukup.')->assertSee('Kirim ulang bukti');
            $this->jeda($browser, 800);

            $browser->attach('photo', $this->fotoPng(6));
            $this->jeda($browser, 500);
            $browser->press('Kirim ulang')->waitForText('Diterima');
            $this->jeda($browser, 1500);
        });

        $this->assertSame('approved', $klaim->fresh()->status);
    }

    public function test_mahasiswa_tidak_bisa_melihat_klaim_milik_orang_lain(): void
    {
        $pemilik = $this->mahasiswa();
        $intip = $this->mahasiswa();
        $challenge = $this->challenge();
        $klaim = Claim::create([
            'user_id' => $pemilik->id,
            'challenge_id' => $challenge->id,
            'proof_path' => 'claims/x.png',
            'photo_hash' => hash('sha256', 'milik-orang'),
            'needs_review' => true,
            'claim_date' => today()->toDateString(),
            'status' => Claim::STATUS_PENDING,
        ]);

        $this->browse(function (Browser $browser) use ($intip, $klaim) {
            $this->masuk($browser, $intip);
            $browser->waitForLocation('/dashboard')->visit('/claims/'.$klaim->id);
            $this->jeda($browser, 800);
            // Klaim milik orang lain disembunyikan (404), bukan 403, agar keberadaannya tidak bocor.
            $browser->assertSee('404');
        });
    }
}
