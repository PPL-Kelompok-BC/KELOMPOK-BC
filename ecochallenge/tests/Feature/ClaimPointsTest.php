<?php

namespace Tests\Feature;

use App\Exceptions\ClaimAlreadyReviewedException;
use App\Models\Category;
use App\Models\Challenge;
use App\Models\Claim;
use App\Models\User;
use App\Services\ClaimService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use Tests\TestCase;

class ClaimPointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Cache::flush();
    }

    private function challenge(int $points = 10, int $co2 = 250): Challenge
    {
        $category = Category::create(['name' => 'Uji '.uniqid(), 'description' => 'Kategori uji']);

        return Challenge::create([
            'category_id' => $category->id,
            'title' => 'Challenge uji '.uniqid(),
            'description' => 'Deskripsi',
            'difficulty' => 'mudah',
            'points' => $points,
            'is_active' => true,
            'proof_criteria' => 'Foto bukti',
            'estimated_co2e_gram' => $co2,
        ]);
    }

    private function pendingClaim(User $user, Challenge $challenge): Claim
    {
        return Claim::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'claim_date' => today()->toDateString(),
            'status' => Claim::STATUS_PENDING,
            'needs_review' => true,
        ]);
    }

    private function service(): ClaimService
    {
        return app(ClaimService::class);
    }

    // PBI-19 alur normal
    public function test_klaim_bernilai_10_poin_menaikkan_total_poin_dari_20_menjadi_30(): void
    {
        $mahasiswa = User::factory()->create(['total_points' => 20]);
        $claim = $this->pendingClaim($mahasiswa, $this->challenge(10));

        $this->service()->approve($claim);

        $this->assertSame(30, $mahasiswa->fresh()->total_points);
    }

    public function test_klaim_dicatat_disetujui_beserta_poin_dan_peninjau(): void
    {
        $mahasiswa = User::factory()->create(['total_points' => 0]);
        $admin = User::factory()->create(['role' => 'admin']);
        $claim = $this->pendingClaim($mahasiswa, $this->challenge(15, 400));

        $this->service()->approve($claim, $admin);

        $claim->refresh();
        $this->assertSame(Claim::STATUS_APPROVED, $claim->status);
        $this->assertSame(15, $claim->points_awarded);
        $this->assertSame(400, $claim->co2e_awarded_gram);
        $this->assertSame($admin->id, $claim->reviewed_by);
        $this->assertNotNull($claim->reviewed_at);
        $this->assertFalse($claim->needs_review);
    }

    public function test_poin_mengikuti_nilai_challenge_masing_masing(): void
    {
        $mahasiswa = User::factory()->create(['total_points' => 5]);

        $this->service()->approve($this->pendingClaim($mahasiswa, $this->challenge(10)));
        $this->service()->approve($this->pendingClaim($mahasiswa, $this->challenge(25)));

        $this->assertSame(40, $mahasiswa->fresh()->total_points);
    }

    public function test_poin_hanya_masuk_ke_pemilik_klaim(): void
    {
        $pemilik = User::factory()->create(['total_points' => 0]);
        $lain = User::factory()->create(['total_points' => 0]);

        $this->service()->approve($this->pendingClaim($pemilik, $this->challenge(10)));

        $this->assertSame(10, $pemilik->fresh()->total_points);
        $this->assertSame(0, $lain->fresh()->total_points);
    }

    // PBI-19 alur eksepsi
    public function test_bila_penyimpanan_klaim_gagal_total_poin_tidak_berubah(): void
    {
        $mahasiswa = User::factory()->create(['total_points' => 20]);
        $claim = $this->pendingClaim($mahasiswa, $this->challenge(10));

        Claim::updating(function () {
            throw new RuntimeException('penyimpanan klaim gagal');
        });

        try {
            $this->service()->approve($claim);
            $this->fail('Pengecualian penyimpanan klaim seharusnya diteruskan.');
        } catch (RuntimeException $e) {
            $this->assertSame('penyimpanan klaim gagal', $e->getMessage());
        }

        $this->assertSame(20, $mahasiswa->fresh()->total_points);
        $this->assertSame(Claim::STATUS_PENDING, $claim->fresh()->status);
        $this->assertSame(0, $claim->fresh()->points_awarded);
    }

    public function test_klaim_yang_sudah_disetujui_tidak_menambah_poin_dua_kali(): void
    {
        $mahasiswa = User::factory()->create(['total_points' => 20]);
        $claim = $this->pendingClaim($mahasiswa, $this->challenge(10));

        $this->service()->approve($claim);

        $this->expectException(ClaimAlreadyReviewedException::class);
        try {
            $this->service()->approve($claim);
        } finally {
            $this->assertSame(30, $mahasiswa->fresh()->total_points);
        }
    }

    public function test_klaim_yang_ditolak_tidak_bisa_disetujui_dan_tidak_menambah_poin(): void
    {
        $mahasiswa = User::factory()->create(['total_points' => 20]);
        $claim = $this->pendingClaim($mahasiswa, $this->challenge(10));
        $claim->update(['status' => Claim::STATUS_REJECTED, 'cancel_reason' => 'Foto buram']);

        $this->expectException(ClaimAlreadyReviewedException::class);
        try {
            $this->service()->approve($claim);
        } finally {
            $this->assertSame(20, $mahasiswa->fresh()->total_points);
            $this->assertSame(Claim::STATUS_REJECTED, $claim->fresh()->status);
        }
    }

    // Terkait PBI-22: poin baru langsung terlihat di leaderboard
    public function test_poin_baru_langsung_tampil_di_leaderboard(): void
    {
        $saya = User::factory()->create(['username' => 'saya', 'email' => 'saya@example.com', 'total_points' => 20]);
        User::factory()->create(['username' => 'lawan', 'email' => 'lawan@example.com', 'total_points' => 25]);

        $this->actingAs($saya)->get('/leaderboard')->assertSee('#2');

        $this->service()->approve($this->pendingClaim($saya, $this->challenge(10)));

        // Pengguna dimuat ulang seperti pada request sungguhan (objek lama masih membawa 20 poin).
        $this->actingAs($saya->fresh())->get('/leaderboard')->assertSee('#1')->assertSee('30');
    }
}
