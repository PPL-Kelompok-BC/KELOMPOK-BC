<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Challenge;
use App\Models\Claim;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminClaimReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function mahasiswa(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role' => User::ROLE_MAHASISWA,
        ], $attributes));
    }

    private function challenge(array $override = []): Challenge
    {
        $category = Category::firstOrCreate(['name' => 'Energi']);

        return Challenge::create(array_merge([
            'category_id' => $category->id,
            'created_by' => User::factory()->create()->id,
            'title' => 'Bawa tumbler',
            'description' => 'Deskripsi',
            'difficulty' => 'mudah',
            'points' => 20,
            'is_active' => true,
            'proof_criteria' => 'Foto tumbler',
            'estimated_co2e_gram' => 83,
            'emission_source' => 'Contoh',
        ], $override));
    }

    private function claim(User $user, Challenge $challenge, string $status = Claim::STATUS_PENDING): Claim
    {
        static $counter = 0;
        $counter++;

        return Claim::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'proof_path' => 'claims/'.$user->id.'/foto-'.$counter.'.jpg',
            'photo_hash' => hash('sha256', 'x'.$counter),
            'claim_date' => today()->toDateString(),
            'status' => $status,
            'needs_review' => $status === Claim::STATUS_PENDING,
        ]);
    }

    public function test_mahasiswa_tidak_bisa_mengakses_halaman_tinjau(): void
    {
        $this->actingAs($this->mahasiswa())
            ->get(route('admin.claims.index'))
            ->assertForbidden();
    }

    public function test_admin_melihat_daftar_dengan_pending_di_atas(): void
    {
        $admin = $this->admin();
        $mahasiswa = $this->mahasiswa();
        $challenge = $this->challenge();

        $this->claim($mahasiswa, $challenge, Claim::STATUS_APPROVED);
        $pending = $this->claim($mahasiswa, $this->challenge(['title' => 'Aksi Lain']), Claim::STATUS_PENDING);

        $this->actingAs($admin)
            ->get(route('admin.claims.index'))
            ->assertOk()
            ->assertSee($challenge->title);

        $first = Claim::orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")->latest()->first();

        $this->assertSame($pending->id, $first->id);
    }

    public function test_approve_memberi_poin_dan_award_sinkron(): void
    {
        $admin = $this->admin();
        $mahasiswa = $this->mahasiswa(['total_points' => 0]);
        $challenge = $this->challenge();
        $claim = $this->claim($mahasiswa, $challenge);

        $this->actingAs($admin)
            ->post(route('admin.claims.review', $claim), ['decision' => 'approve'])
            ->assertRedirect(route('admin.claims.index'));

        $claim->refresh();

        $this->assertSame(Claim::STATUS_APPROVED, $claim->status);
        $this->assertSame(20, $claim->points_awarded);
        $this->assertSame(83, $claim->co2e_awarded_gram);
        $this->assertFalse($claim->needs_review);
        $this->assertSame($admin->id, $claim->reviewed_by);
        $this->assertSame(20, $mahasiswa->fresh()->total_points);
    }

    public function test_reject_wajib_alasan_dan_reset_award(): void
    {
        $admin = $this->admin();
        $mahasiswa = $this->mahasiswa();
        $challenge = $this->challenge();
        $claim = $this->claim($mahasiswa, $challenge);

        $this->actingAs($admin)
            ->post(route('admin.claims.review', $claim), ['decision' => 'reject'])
            ->assertSessionHasErrors('cancel_reason');

        $this->actingAs($admin)
            ->post(route('admin.claims.review', $claim), [
                'decision' => 'reject',
                'cancel_reason' => 'Foto tidak jelas.',
            ])
            ->assertRedirect(route('admin.claims.index'));

        $claim->refresh();

        $this->assertSame(Claim::STATUS_REJECTED, $claim->status);
        $this->assertSame('Foto tidak jelas.', $claim->cancel_reason);
        $this->assertSame(0, $claim->points_awarded);
        $this->assertSame(0, $claim->co2e_awarded_gram);
        $this->assertSame(0, $mahasiswa->fresh()->total_points);
    }

    public function test_klaim_non_pending_tidak_bisa_direview_lagi(): void
    {
        $admin = $this->admin();
        $mahasiswa = $this->mahasiswa();
        $challenge = $this->challenge();
        $claim = $this->claim($mahasiswa, $challenge, Claim::STATUS_APPROVED);

        $this->actingAs($admin)
            ->post(route('admin.claims.review', $claim), ['decision' => 'approve'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(Claim::STATUS_APPROVED, $claim->fresh()->status);
        $this->assertSame(0, $claim->fresh()->points_awarded);
    }
}
