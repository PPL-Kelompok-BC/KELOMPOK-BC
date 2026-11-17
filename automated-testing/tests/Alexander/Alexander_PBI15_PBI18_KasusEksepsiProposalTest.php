<?php

namespace Tests\Browser\Fitur\Alexander;

use App\Models\Claim;
use Laravel\Dusk\Browser;
use Tests\Browser\Fitur\FiturTestCase;

/**
 * Alexander – melengkapi kriteria penerimaan proposal untuk PBI-15 s.d. PBI-18
 * (kartu Jira SCRUM-55 s.d. 58 belum berisi test case, jadi acuannya proposal Tabel 5.1).
 */
class Alexander_PBI15_PBI18_KasusEksepsiProposalTest extends FiturTestCase
{
    /** PBI-15 Eksepsi: berkas PDF ditolak dengan alasan yang jelas. */
    public function test_pbi15_eksepsi_berkas_pdf_ditolak_dengan_alasan_jelas(): void
    {
        $user = $this->mahasiswa();
        $challenge = $this->challenge();

        $this->browse(function (Browser $browser) use ($user, $challenge) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/challenges/'.$challenge->id.'/claims')
                ->attach('photo', $this->berkasPdf());
            $this->jeda($browser, 600);
            $browser->press('Kirim bukti')->waitForText('Foto bukti harus berformat JPG atau PNG.');
            $this->jeda($browser, 1000);
        });

        $this->assertSame(0, Claim::count());
    }

    /** PBI-15 Eksepsi: berkas di atas 2 MB ditolak dengan alasan yang jelas. */
    public function test_pbi15_eksepsi_berkas_lebih_dari_2_mb_ditolak_dengan_alasan_jelas(): void
    {
        $user = $this->mahasiswa();
        $challenge = $this->challenge();

        $this->browse(function (Browser $browser) use ($user, $challenge) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/challenges/'.$challenge->id.'/claims')
                ->attach('photo', $this->fotoPngBesar());
            $this->jeda($browser, 800);
            $browser->press('Kirim bukti')->waitForText('maksimal 2 MB');
            $this->jeda($browser, 1000);
        });

        $this->assertSame(0, Claim::count());
    }

    /** PBI-15 Normal: dari detail challenge, pilih foto, kirim, lalu halaman hasil klaim terbuka (3 langkah). */
    public function test_pbi15_normal_dari_detail_pilih_foto_kirim_dan_hasil_klaim_terbuka(): void
    {
        $user = $this->mahasiswa();
        $challenge = $this->challenge(null, ['title' => 'Matikan Lampu']);

        $this->browse(function (Browser $browser) use ($user, $challenge) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/challenges/'.$challenge->id)->waitForText('Matikan Lampu');
            $this->jeda($browser);

            $browser->clickLink('Kirim bukti');                                  // langkah 1: buka formulir
            $browser->attach('photo', $this->fotoPng(41));                       // langkah 2: pilih foto
            $this->jeda($browser, 600);
            $browser->press('Kirim bukti')->waitForText('Menunggu verifikasi');   // langkah 3: kirim
            $browser->assertPathBeginsWith('/claims/')->assertSee('Matikan Lampu');
            $this->jeda($browser, 1200);
        });
    }

    /** PBI-16 Normal: foto baru dengan tanggal EXIF hari ini diterima dan waktu pengambilannya terbaca. */
    public function test_pbi16_normal_foto_dengan_exif_hari_ini_diterima(): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('Ekstensi GD belum aktif; JPEG uji dibuat dengan GD.');
        }
        $user = $this->mahasiswa();
        $challenge = $this->challenge();

        $this->browse(function (Browser $browser) use ($user, $challenge) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/challenges/'.$challenge->id.'/claims')
                ->attach('photo', $this->fotoJpeg(42, now()->format('Y-m-d H:i:s')));
            $this->jeda($browser, 600);
            $browser->press('Kirim bukti')->waitForText('Menunggu verifikasi');
            $this->jeda($browser, 1200);
        });

        $klaim = Claim::firstOrFail();
        $this->assertNotNull($klaim->photo_taken_at, 'Tanggal EXIF seharusnya terbaca dari foto.');
        $this->assertSame(now()->toDateString(), $klaim->photo_taken_at->toDateString());
        $this->assertSame('pending', $klaim->status);
    }

    /** PBI-18 Eksepsi: foto tanpa EXIF tetap diterima dan diberi tanda tinjau. */
    public function test_pbi18_eksepsi_foto_tanpa_exif_tetap_diterima_dan_diberi_tanda_tinjau(): void
    {
        $user = $this->mahasiswa();
        $challenge = $this->challenge();

        $this->browse(function (Browser $browser) use ($user, $challenge) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/challenges/'.$challenge->id.'/claims')
                ->attach('photo', $this->fotoPng(43));
            $this->jeda($browser, 600);
            $browser->press('Kirim bukti')->waitForText('Menunggu verifikasi');
            $this->jeda($browser, 1200);
        });

        $klaim = Claim::firstOrFail();
        $this->assertNull($klaim->photo_taken_at);
        $this->assertTrue((bool) $klaim->needs_review, 'Foto tanpa EXIF harus ditandai untuk ditinjau.');
        $this->assertSame('pending', $klaim->status);
    }

    /** PBI-17 Normal: pemilik klaim bisa membuka foto buktinya dari halaman riwayat. */
    public function test_pbi17_normal_pemilik_membuka_foto_bukti_dari_riwayat(): void
    {
        $user = $this->mahasiswa();
        $challenge = $this->challenge(null, ['title' => 'Naik Angkot']);

        $this->browse(function (Browser $browser) use ($user, $challenge) {
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->visit('/challenges/'.$challenge->id.'/claims')
                ->attach('photo', $this->fotoPng(44))->press('Kirim bukti')->waitForText('Menunggu verifikasi');
            $this->jeda($browser);

            $browser->visit('/claims')->waitForText('Naik Angkot')->clickLink('Naik Angkot')->waitForText('Foto bukti');
            $browser->waitFor('img[src*="/photo"]');
            $this->jeda($browser, 1000);
            $terbaca = $browser->script('return [...document.images].some(i => i.src.includes("/photo") && i.complete && i.naturalWidth > 0);');
            $this->assertTrue((bool) $terbaca[0], 'Foto bukti harus tampil (gambar terunduh).');
        });
    }

    /**
     * PBI-17 Eksepsi: mahasiswa lain yang membuka URL foto tidak bisa melihatnya.
     * Catatan: proposal menyebut respons 403; aplikasi memberi 404 agar keberadaan klaim tidak bocor.
     */
    public function test_pbi17_eksepsi_mahasiswa_lain_tidak_bisa_membuka_url_foto(): void
    {
        $pemilik = $this->mahasiswa();
        $intip = $this->mahasiswa();
        $challenge = $this->challenge();

        $this->browse(function (Browser $browser) use ($pemilik, $intip, $challenge) {
            $this->masuk($browser, $pemilik);
            $browser->waitForLocation('/dashboard')->visit('/challenges/'.$challenge->id.'/claims')
                ->attach('photo', $this->fotoPng(45))->press('Kirim bukti')->waitForText('Menunggu verifikasi');
            $klaimId = Claim::firstOrFail()->id;
            $this->jeda($browser);

            $browser->logout();
            $this->masuk($browser, $intip);
            $browser->waitForLocation('/dashboard')->visit('/claims/'.$klaimId.'/photo');
            $this->jeda($browser, 800);
            $browser->assertSee('404');
        });
    }
}
