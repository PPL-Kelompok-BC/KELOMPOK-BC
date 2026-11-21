<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Challenge;
use App\Models\Claim;
use App\Models\User;
use App\Services\LeaderboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * PBI-19 (SCRUM-31): admin menyetujui klaim lewat halaman Tinjau Klaim, lalu poin
 * mahasiswa bertambah otomatis di basis data dan leaderboard ikut berubah.
 */
class AdminClaimReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Cache::flush();
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function mahasiswa(int $poin = 20): User
    {
        return User::factory()->create(['role' => User::ROLE_MAHASISWA, 'is_active' => true, 'total_points' => $poin]);
    }

    private function klaim(User $user, int $poin = 10, string $status = Claim::STATUS_PENDING): Claim
    {
        $category = Category::create(['name' => 'Uji '.uniqid(), 'description' => 'Kategori uji']);
        $challenge = Challenge::create([
            'category_id' => $category->id,
            'title' => 'Pisahkan Sampah',
            'description' => 'Deskripsi',
            'difficulty' => 'mudah',
            'points' => $poin,
            'is_active' => true,
            'proof_criteria' => 'Foto bukti',
            'estimated_co2e_gram' => 200,
        ]);

        return Claim::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'claim_date' => today()->toDateString(),
            'status' => $status,
            'needs_review' => true,
        ]);
    }

    public function test_admin_melihat_klaim_yang_menunggu_tinjauan(): void
    {
        $mahasiswa = $this->mahasiswa();
        $menunggu = $this->klaim($mahasiswa);
        $this->klaim($mahasiswa, 15, Claim::STATUS_APPROVED);

        $this->actingAs($this->admin())
            ->get('/admin/claims')
            ->assertOk()
            ->assertSee('Tinjau klaim.')
            ->assertSee($mahasiswa->username)
            ->assertSee('Setujui (+10 poin)')
            ->assertSee(route('admin.claims.approve', $menunggu))
            ->assertDontSee('Setujui (+15 poin)');
    }

    public function test_tidak_ada_klaim_menunggu_menampilkan_pesan_kosong(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/claims')
            ->assertOk()
            ->assertSee('Tidak ada klaim menunggu');
    }

    /** TC-19-01 lewat web: total poin 20 menjadi 30 dan klaim tercatat disetujui oleh admin. */
    public function test_admin_menyetujui_klaim_menambah_poin_mahasiswa(): void
    {
        $admin = $this->admin();
        $mahasiswa = $this->mahasiswa(20);
        $klaim = $this->klaim($mahasiswa, 10);

        $this->actingAs($admin)
            ->post(route('admin.claims.approve', $klaim))
            ->assertRedirect(route('admin.claims.index'))
            ->assertSessionHas('status', "Klaim disetujui. {$mahasiswa->username} mendapat +10 poin.");

        $this->assertSame(30, $mahasiswa->fresh()->total_points);
        $klaim->refresh();
        $this->assertSame(Claim::STATUS_APPROVED, $klaim->status);
        $this->assertSame(10, $klaim->points_awarded);
        $this->assertSame($admin->id, $klaim->reviewed_by);
        $this->assertNotNull($klaim->reviewed_at);
    }

    /** TC-20-06 lewat web: setelah disetujui, leaderboard langsung memakai poin baru. */
    public function test_leaderboard_langsung_berubah_setelah_admin_menyetujui(): void
    {
        $mahasiswa = $this->mahasiswa(20);
        $lawan = $this->mahasiswa(25);
        $klaim = $this->klaim($mahasiswa, 10);
        $leaderboard = app(LeaderboardService::class);

        $this->assertSame($lawan->id, $leaderboard->top()[0]['id']);

        $this->actingAs($this->admin())->post(route('admin.claims.approve', $klaim));

        $this->assertSame($mahasiswa->id, $leaderboard->top()[0]['id']);
        $this->assertSame(30, $leaderboard->top()[0]['total_points']);
    }

    /** TC-19-04 lewat web: menekan Setujui dua kali tidak menambah poin dua kali. */
    public function test_menyetujui_dua_kali_ditolak_dan_poin_tidak_ganda(): void
    {
        $admin = $this->admin();
        $mahasiswa = $this->mahasiswa(20);
        $klaim = $this->klaim($mahasiswa, 10);

        $this->actingAs($admin)->post(route('admin.claims.approve', $klaim));
        $this->actingAs($admin)
            ->post(route('admin.claims.approve', $klaim))
            ->assertRedirect(route('admin.claims.index'))
            ->assertSessionHasErrors(['claim' => 'Klaim ini sudah direview.']);

        $this->assertSame(30, $mahasiswa->fresh()->total_points);
    }

    public function test_mahasiswa_tidak_bisa_membuka_atau_menyetujui_klaim(): void
    {
        $mahasiswa = $this->mahasiswa(20);
        $klaim = $this->klaim($mahasiswa, 10);

        $this->actingAs($mahasiswa)->get('/admin/claims')->assertForbidden();
        $this->actingAs($mahasiswa)->post(route('admin.claims.approve', $klaim))->assertForbidden();

        $this->assertSame(20, $mahasiswa->fresh()->total_points);
        $this->assertSame(Claim::STATUS_PENDING, $klaim->fresh()->status);
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/admin/claims')->assertRedirect('/login');
    }
}
