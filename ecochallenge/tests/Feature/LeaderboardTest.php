<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\LeaderboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Tests\TestCase;

class LeaderboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Cache::flush();
    }

    private function mhs(string $username, int $points, array $extra = []): User
    {
        return User::factory()->create(array_merge([
            'username' => $username,
            'email' => $username.'@example.com',
            'name' => 'Nama Asli '.$username,
            'total_points' => $points,
        ], $extra));
    }

    /** @return list<string> username sesuai urutan baris (podium lalu daftar) */
    private function usernamesInOrder(string $html): array
    {
        // Setiap baris peringkat membawa atribut data-username.
        preg_match_all('/<li[^>]*\sdata-row\s[^>]*data-username="([^"]+)"/', $html, $m);

        return $m[1];
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/leaderboard')->assertRedirect(route('login'));
    }

    // PBI-20 alur normal
    public function test_mahasiswa_dengan_poin_tertinggi_berada_di_urutan_pertama(): void
    {
        $this->mhs('rendah', 10);
        $this->mhs('tertinggi', 300);
        $tengah = $this->mhs('tengah', 120);

        $response = $this->actingAs($tengah)->get('/leaderboard')->assertOk();

        $this->assertSame(['tertinggi', 'tengah', 'rendah'], $this->usernamesInOrder($response->getContent()));
    }

    public function test_poin_sama_diurutkan_menurut_username(): void
    {
        $b = $this->mhs('budi', 50);
        $this->mhs('andi', 50);
        $this->mhs('citra', 50);

        $response = $this->actingAs($b)->get('/leaderboard')->assertOk();

        $this->assertSame(['andi', 'budi', 'citra'], $this->usernamesInOrder($response->getContent()));
    }

    // PBI-20 alur eksepsi
    public function test_akun_nonaktif_tidak_tampil_di_leaderboard(): void
    {
        $aktif = $this->mhs('aktif', 20);
        $this->mhs('dinonaktifkan', 999, ['is_active' => false]);

        $response = $this->actingAs($aktif)->get('/leaderboard')->assertOk();

        $response->assertDontSee('dinonaktifkan');
        $this->assertSame(['aktif'], $this->usernamesInOrder($response->getContent()));
    }

    public function test_akun_admin_tidak_tampil_di_leaderboard(): void
    {
        $mhs = $this->mhs('mahasiswa', 5);
        User::factory()->create(['username' => 'adminku', 'email' => 'a@example.com', 'role' => 'admin', 'total_points' => 500]);

        $this->actingAs($mhs)->get('/leaderboard')->assertOk()->assertDontSee('adminku');
    }

    public function test_menampilkan_username_bukan_nama_asli(): void
    {
        $this->mhs('pengguna.satu', 10, ['name' => 'Budi Santoso Rahasia']);
        $pengamat = $this->mhs('pengamat', 5, ['name' => 'Pengamat Biasa']);

        // Nama asli peserta lain tidak boleh bocor; hanya username yang tampil di papan.
        $this->actingAs($pengamat)->get('/leaderboard')
            ->assertOk()
            ->assertSee('pengguna.satu')
            ->assertDontSee('Budi Santoso Rahasia');
    }

    public function test_keadaan_kosong_bila_belum_ada_mahasiswa_aktif(): void
    {
        $admin = User::factory()->create(['username' => 'adm', 'email' => 'adm@example.com', 'role' => 'admin']);

        $this->actingAs($admin)->get('/leaderboard')->assertOk()->assertSee('Belum ada peringkat');
    }

    // PBI-21 alur normal
    public function test_baris_pengguna_yang_login_diberi_penanda(): void
    {
        $this->mhs('lain', 100);
        $saya = $this->mhs('saya', 40);

        $response = $this->actingAs($saya)->get('/leaderboard')->assertOk();

        $response->assertSee('data-me="true"', false)->assertSee('KAMU', false)->assertSee('POSISI KAMU')->assertSee('#2');
        $this->assertSame(1, substr_count($response->getContent(), 'data-me="true"'));
    }

    // PBI-21 alur eksepsi
    public function test_pengguna_di_luar_50_besar_melihat_barisnya_di_bawah_daftar(): void
    {
        for ($i = 1; $i <= 55; $i++) {
            $this->mhs(sprintf('peserta%02d', $i), 1000 - $i);
        }
        $saya = $this->mhs('terakhir', 1);

        $response = $this->actingAs($saya)->get('/leaderboard')->assertOk();
        $order = $this->usernamesInOrder($response->getContent());

        $this->assertCount(51, $order);              // 50 besar + baris sendiri
        $this->assertSame('terakhir', end($order));
        $response->assertSee('#56');                 // peringkat sebenarnya
        $this->assertSame(1, substr_count($response->getContent(), 'data-me="true"'));
    }

    public function test_pengguna_di_50_besar_tidak_memiliki_baris_ganda(): void
    {
        for ($i = 1; $i <= 55; $i++) {
            $this->mhs(sprintf('peserta%02d', $i), 1000 - $i);
        }

        $response = $this->actingAs(User::where('username', 'peserta03')->first())->get('/leaderboard')->assertOk();

        $this->assertCount(50, $this->usernamesInOrder($response->getContent()));
        $this->assertSame(1, substr_count($response->getContent(), 'data-me="true"'));
    }

    public function test_admin_tidak_punya_baris_atau_posisi(): void
    {
        $this->mhs('peserta', 10);
        $admin = User::factory()->create(['username' => 'adm', 'email' => 'adm@example.com', 'role' => 'admin']);

        $this->actingAs($admin)->get('/leaderboard')->assertOk()
            ->assertDontSee('POSISI KAMU')
            ->assertDontSee('data-me="true"', false);
    }

    // PBI-22 alur normal
    public function test_poin_baru_langsung_tampil_setelah_refresh(): void
    {
        $saya = $this->mhs('saya', 20);
        $this->mhs('lawan', 30);

        $this->actingAs($saya)->get('/leaderboard')->assertSee('#2');

        $saya->update(['total_points' => 50]);
        app(LeaderboardService::class)->refresh();

        $this->actingAs($saya)->get('/leaderboard')->assertSee('#1');
    }

    public function test_umur_cache_tidak_melebihi_dua_detik(): void
    {
        $this->assertLessThanOrEqual(2, LeaderboardService::CACHE_TTL_SECONDS);
    }

    // PBI-22 alur eksepsi
    public function test_bila_cache_gagal_leaderboard_dihitung_dari_basis_data(): void
    {
        $saya = $this->mhs('saya', 20);
        $this->mhs('lawan', 30);

        Cache::partialMock()->shouldReceive('remember')->andThrow(new RuntimeException('cache mati'));
        $saya->update(['total_points' => 77]);

        $this->actingAs($saya)->get('/leaderboard')
            ->assertOk()
            ->assertSee('77')
            ->assertSee('#1');
    }

    public function test_kegagalan_refresh_cache_tidak_menjatuhkan_halaman(): void
    {
        $saya = $this->mhs('saya', 20);

        Cache::partialMock()->shouldReceive('forget')->andThrow(new RuntimeException('cache mati'));
        app(LeaderboardService::class)->refresh();

        $this->actingAs($saya)->get('/leaderboard')->assertOk();
    }

    public function test_rank_of_menghitung_peringkat_dari_basis_data(): void
    {
        $this->mhs('a', 100);
        $b = $this->mhs('b', 100);
        $c = $this->mhs('c', 10);
        $service = app(LeaderboardService::class);

        $this->assertSame(2, $service->rankOf($b));
        $this->assertSame(3, $service->rankOf($c));
        $this->assertNull($service->rankOf(User::factory()->create(['username' => 'x', 'email' => 'x@example.com', 'role' => 'admin'])));
    }
}
