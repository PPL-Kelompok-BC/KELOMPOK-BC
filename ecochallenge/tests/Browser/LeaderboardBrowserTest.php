<?php

namespace Tests\Browser;

use App\Models\Category;
use App\Models\Challenge;
use App\Models\Claim;
use App\Models\User;
use App\Services\LeaderboardService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Cache;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * PBI-19 · Jonathan – Poin otomatis saat klaim diterima (SCRUM-31, TC-19-xx) dan
 * PBI-20 · Jonathan – Leaderboard total poin, posisi sendiri, dan pembaruan maks. 2 detik
 * (SCRUM-32, TC-20-xx; gabungan PBI-20, PBI-21/SCRUM-33, dan PBI-22/SCRUM-34).
 *
 * Poin bertambah lewat alur sungguhan di web: admin membuka Tinjau Klaim lalu menekan Setujui,
 * dan mahasiswa melihat total poin serta leaderboard berubah dari basis data.
 * Skenario eksepsi yang tidak bisa dipicu dari browser (penyimpanan klaim gagal, cache gagal)
 * diuji di tests/Feature/ClaimPointsTest.php dan tests/Feature/LeaderboardTest.php.
 */
class LeaderboardBrowserTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        // Cache leaderboard (umur 2 detik) dari tes sebelumnya jangan sampai terbawa.
        Cache::flush();
    }

    private function mahasiswa(string $username, int $poin, array $atribut = []): User
    {
        return User::factory()->create(array_merge([
            'name' => 'Nama Asli '.$username,
            'username' => $username,
            'email' => $username.'@student.telkomuniversity.ac.id',
            'role' => User::ROLE_MAHASISWA,
            'is_active' => true,
            'total_points' => $poin,
        ], $atribut));
    }

    private function klaimMenunggu(User $user, int $poinChallenge): Claim
    {
        $kategori = Category::create(['name' => 'Kategori '.uniqid()]);
        $challenge = Challenge::create([
            'category_id' => $kategori->id,
            'title' => 'Pisahkan Sampah',
            'description' => 'Memilah sampah organik dan anorganik.',
            'difficulty' => 'mudah',
            'points' => $poinChallenge,
            'is_active' => true,
            'proof_criteria' => 'Foto dua tempat sampah terpisah.',
            'estimated_co2e_gram' => 200,
            'emission_source' => 'Estimasi uji',
        ]);

        return Claim::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'proof_path' => 'claim-proofs/uji.png',
            'photo_hash' => hash('sha256', uniqid('', true)),
            'needs_review' => true,
            'claim_date' => today()->toDateString(),
            'status' => Claim::STATUS_PENDING,
        ]);
    }

    private function admin(): User
    {
        return User::factory()->create(['name' => 'Admin EcoChallenge', 'username' => 'admin.uji', 'email' => 'admin.uji@ecochallenge.test', 'role' => User::ROLE_ADMIN]);
    }

    /** Admin membuka Tinjau Klaim dan menekan tombol Setujui pada klaim tertentu. */
    private function setujuiLewatWeb(Browser $browser, User $admin, Claim $klaim): Browser
    {
        return $browser->loginAs($admin)
            ->visit('/admin/claims')
            ->waitForText('Tinjau klaim.')
            ->assertSeeIn('@claim-'.$klaim->id, $klaim->user->username)
            ->press('@approve-'.$klaim->id)
            ->waitFor('@claim-review-status')
            ->assertSeeIn('@claim-review-status', 'Klaim disetujui.');
    }

    /** TC-19-01 Normal: admin menyetujui klaim 10 poin di web, total poin mahasiswa naik dari 20 menjadi 30. */
    public function test_pbi19_klaim_10_poin_disetujui_menaikkan_total_poin_20_menjadi_30(): void
    {
        $saya = $this->mahasiswa('rani.hijau', 20);
        $admin = $this->admin();
        $klaim = $this->klaimMenunggu($saya, 10);

        $this->browse(function (Browser $mahasiswa, Browser $petugas) use ($saya, $admin, $klaim) {
            $mahasiswa->loginAs($saya)->visit('/dashboard')->waitForText('TOTAL POIN')->assertSee('20');

            $this->setujuiLewatWeb($petugas, $admin, $klaim)
                ->assertSeeIn('@claim-review-status', 'rani.hijau mendapat +10 poin')
                ->assertMissing('@claim-'.$klaim->id);

            $mahasiswa->refresh()->waitForText('TOTAL POIN')->assertSee('30');
        });

        $this->assertSame(30, $saya->fresh()->total_points);
        $this->assertSame(Claim::STATUS_APPROVED, $klaim->fresh()->status);
        $this->assertSame(10, $klaim->fresh()->points_awarded);
        $this->assertSame($admin->id, $klaim->fresh()->reviewed_by);
    }

    /** PBI-20 Normal: total poin tertinggi berada di urutan pertama. Eksepsi: akun nonaktif tidak tampil. */
    public function test_pbi20_urutan_berdasarkan_total_poin_dan_akun_nonaktif_tidak_tampil(): void
    {
        $this->mahasiswa('juara.satu', 900);
        $this->mahasiswa('juara.dua', 700);
        $saya = $this->mahasiswa('juara.tiga', 500);
        $this->mahasiswa('akun.nonaktif', 9999, ['is_active' => false]);
        User::factory()->create(['username' => 'admin.uji', 'email' => 'admin.uji@ecochallenge.test', 'role' => User::ROLE_ADMIN, 'total_points' => 9999]);

        $this->browse(function (Browser $browser) use ($saya) {
            $browser->loginAs($saya)->visit('/leaderboard')->waitForText('POSISI KAMU');

            $urutan = $browser->script('return [...document.querySelectorAll("[data-row]")].map(e => e.dataset.username);');
            $this->assertSame(['juara.satu', 'juara.dua', 'juara.tiga'], $urutan[0]);

            $browser->assertDontSee('akun.nonaktif')->assertDontSee('admin.uji');
        });
    }

    /** PBI-21 Normal: baris milik pengguna yang login ditandai dan memakai username, bukan nama asli. */
    public function test_pbi21_baris_sendiri_ditandai_dengan_username(): void
    {
        $this->mahasiswa('lawan.satu', 300);
        $saya = $this->mahasiswa('rani.hijau', 100, ['name' => 'Rani Nama Asli']);

        $this->browse(function (Browser $browser) use ($saya) {
            $browser->loginAs($saya)->visit('/leaderboard')->waitForText('POSISI KAMU')
                ->assertSee('#2')
                ->assertPresent('[data-me="true"][data-username="rani.hijau"]')
                ->assertSee('KAMU');

            $jumlah = $browser->script('return document.querySelectorAll(\'[data-me="true"]\').length;');
            $this->assertSame(1, (int) $jumlah[0], 'Hanya satu baris yang ditandai sebagai milik sendiri.');

            // Nama asli peserta lain tidak ditampilkan di papan.
            $browser->assertDontSee('Nama Asli lawan.satu');
        });
    }

    /** PBI-21 Eksepsi: pengguna di luar 50 besar tetap melihat baris posisinya di bagian bawah daftar. */
    public function test_pbi21_pengguna_di_luar_50_besar_tetap_melihat_barisnya(): void
    {
        for ($i = 1; $i <= 55; $i++) {
            $this->mahasiswa(sprintf('peserta%02d', $i), 1000 - $i);
        }
        $saya = $this->mahasiswa('paling.bawah', 1);

        $this->browse(function (Browser $browser) use ($saya) {
            $browser->loginAs($saya)->visit('/leaderboard')->waitForText('POSISI KAMU')
                ->assertSee('#56');

            $baris = $browser->script('return [...document.querySelectorAll("[data-row]")].map(e => e.dataset.username);');
            $this->assertCount(51, $baris[0], '50 besar ditambah baris milik sendiri.');
            $this->assertSame('paling.bawah', end($baris[0]));
        });
    }

    /** TC-20-06 Normal: setelah admin menyetujui klaim, leaderboard mahasiswa menampilkan poin baru paling lama 2 detik. */
    public function test_pbi22_poin_baru_langsung_tampil_di_leaderboard_setelah_klaim_diterima(): void
    {
        $saya = $this->mahasiswa('rani.hijau', 20);
        $this->mahasiswa('lawan.satu', 25);
        $admin = $this->admin();
        $klaim = $this->klaimMenunggu($saya, 10);

        $this->browse(function (Browser $mahasiswa, Browser $petugas) use ($saya, $admin, $klaim) {
            $mahasiswa->loginAs($saya)->visit('/leaderboard')->waitForText('POSISI KAMU')->assertSee('#2');

            $this->setujuiLewatWeb($petugas, $admin, $klaim);
            $mulai = microtime(true);

            $mahasiswa->refresh()->waitForText('#1', 2)->assertSee('Kamu memimpin papan peringkat');
            $this->assertLessThanOrEqual(2.0, microtime(true) - $mulai, 'Poin baru harus tampil paling lama 2 detik.');
        });

        $this->assertLessThanOrEqual(2, LeaderboardService::CACHE_TTL_SECONDS);
    }
}
