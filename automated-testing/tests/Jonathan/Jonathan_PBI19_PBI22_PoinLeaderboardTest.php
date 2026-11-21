<?php

namespace Tests\Browser\Fitur\Jonathan;

use Tests\Browser\Fitur\FiturTestCase;

use App\Models\Claim;
use App\Models\User;
use App\Services\ClaimService;
use App\Services\LeaderboardService;
use Laravel\Dusk\Browser;

/** Jonathan – PBI-19 s.d. PBI-22: poin otomatis, leaderboard, penanda posisi sendiri, dan pembaruan cepat. */
class Jonathan_PBI19_PBI22_PoinLeaderboardTest extends FiturTestCase
{
    public function test_poin_bertambah_otomatis_dan_peringkat_naik_setelah_klaim_disetujui(): void
    {
        // Skenario proposal PBI-19: klaim challenge bernilai 10 poin menaikkan total poin dari 20 menjadi 30.
        $user = $this->mahasiswa(['total_points' => 20]);
        $this->mahasiswa(['total_points' => 50]);
        $admin = $this->admin();
        $challenge = $this->challenge(null, ['title' => 'Pisahkan Sampah', 'points' => 10]);

        $this->browse(function (Browser $browser) use ($user, $admin, $challenge) {
            // 1) Mahasiswa mengirim bukti lewat browser.
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')
                ->visit('/challenges/'.$challenge->id.'/claims')
                ->attach('photo', $this->fotoPng(11))->press('Kirim bukti')
                ->waitForText('Menunggu verifikasi');
            $this->jeda($browser, 1000);

            // Sebelum disetujui: poin 0, peringkat 2 dari 2.
            $browser->visit('/dashboard')->waitForText('#2');
            $this->jeda($browser, 1200);

            // 2) Admin masuk, membuka Tinjau Klaim, lalu menekan Setujui (PBI-19).
            $klaim = Claim::where('user_id', $user->id)->firstOrFail();
            $this->keluar($browser)->waitForLocation('/login');
            $this->masuk($browser, $admin);
            $browser->waitForLocation('/admin')
                ->visit('/admin/claims')->waitForText('Tinjau klaim.')
                ->assertSeeIn('@claim-'.$klaim->id, $user->username);
            $this->jeda($browser, 1000);
            $browser->press('@approve-'.$klaim->id)
                ->waitFor('@claim-review-status')
                ->assertSeeIn('@claim-review-status', $user->username.' mendapat +10 poin');
            $this->jeda($browser, 1200);
            $this->keluar($browser)->waitForLocation('/login');

            // 3) Mahasiswa masuk lagi: poin dan peringkat berubah di dashboard dan leaderboard (PBI-20).
            $this->masuk($browser, $user);
            $browser->waitForLocation('/dashboard')->waitForText('#2')->assertSee('30');
            $this->jeda($browser, 1200);

            $browser->visit('/leaderboard')->waitForText('POSISI KAMU');
            $this->jeda($browser, 1500);
        });

        $this->assertSame(30, $user->fresh()->total_points);
    }

    public function test_leaderboard_mengurutkan_berdasarkan_poin_dan_menandai_posisi_sendiri(): void
    {
        $this->mahasiswa(['username' => 'juara.satu', 'total_points' => 900]);
        $this->mahasiswa(['username' => 'juara.dua', 'total_points' => 700]);
        $this->mahasiswa(['username' => 'juara.tiga', 'total_points' => 500]);
        $saya = $this->mahasiswa(['username' => 'saya.empat', 'name' => 'Nama Asli Saya', 'total_points' => 300]);
        $this->mahasiswa(['username' => 'sudah.nonaktif', 'total_points' => 9999, 'is_active' => false]);
        $this->admin(['username' => 'admin.rahasia', 'total_points' => 9999]);

        $this->browse(function (Browser $browser) use ($saya) {
            $this->masuk($browser, $saya);
            $browser->waitForLocation('/dashboard')->clickLink('Leaderboard')->waitForLocation('/leaderboard');
            $this->jeda($browser, 1200);

            $browser->assertSee('POSISI KAMU')->assertSee('#4')
                ->assertSee('juara.satu')->assertSee('juara.dua')->assertSee('juara.tiga')
                ->assertDontSee('sudah.nonaktif')->assertDontSee('admin.rahasia');

            // Tepat satu baris yang ditandai sebagai milik sendiri.
            $browser->assertPresent('[data-me="true"]');
            $jumlah = $browser->script('return document.querySelectorAll(\'[data-me="true"]\').length;');
            $this->assertSame(1, (int) $jumlah[0]);

            // Urutan baris mengikuti poin tertinggi ke terendah.
            $urutan = $browser->script('return [...document.querySelectorAll("[data-row]")].map(e => e.dataset.username);');
            $this->assertSame(['juara.satu', 'juara.dua', 'juara.tiga', 'saya.empat'], $urutan[0]);
            $this->jeda($browser, 1500);
        });
    }

    public function test_leaderboard_kosong_menampilkan_keadaan_kosong(): void
    {
        $admin = $this->admin();

        $this->browse(function (Browser $browser) use ($admin) {
            $this->masuk($browser, $admin);
            $browser->waitForLocation('/admin')->visit('/leaderboard')
                ->assertSee('Belum ada peringkat')->assertDontSee('POSISI KAMU');
            $this->jeda($browser, 1500);
        });
    }

    public function test_pengguna_di_luar_50_besar_tetap_melihat_barisnya_sendiri(): void
    {
        for ($i = 1; $i <= 55; $i++) {
            $this->mahasiswa(['username' => sprintf('peserta%02d', $i), 'total_points' => 1000 - $i]);
        }
        $saya = $this->mahasiswa(['username' => 'paling.bawah', 'total_points' => 1]);

        $this->browse(function (Browser $browser) use ($saya) {
            $this->masuk($browser, $saya);
            $browser->waitForLocation('/dashboard')->visit('/leaderboard');
            $this->jeda($browser, 1200);

            $browser->assertSee('#56')->assertSee('paling.bawah')->assertSee('Menampilkan 50 besar');
            $jumlah = $browser->script('return document.querySelectorAll("[data-row]").length;');
            $this->assertSame(51, (int) $jumlah[0]); // 50 besar + baris sendiri
            $browser->scrollIntoView('[data-me="true"]');
            $this->jeda($browser, 1500);
        });
    }

    public function test_perubahan_poin_langsung_tampil_setelah_leaderboard_diperbarui(): void
    {
        $saya = $this->mahasiswa(['total_points' => 20]);
        $lawan = $this->mahasiswa(['total_points' => 30]);

        $this->browse(function (Browser $browser) use ($saya) {
            $this->masuk($browser, $saya);
            $browser->waitForLocation('/dashboard')->visit('/leaderboard')->waitForText('#2');
            $this->jeda($browser, 1000);

            $saya->update(['total_points' => 80]);
            app(LeaderboardService::class)->refresh();

            $browser->refresh()->waitForText('#1')->assertSee('Kamu memimpin papan peringkat');
            $this->jeda($browser, 1500);
        });

        $this->assertLessThanOrEqual(2, LeaderboardService::CACHE_TTL_SECONDS);
        $this->assertSame(1, app(LeaderboardService::class)->rankOf(User::find($saya->id)));
    }
}
