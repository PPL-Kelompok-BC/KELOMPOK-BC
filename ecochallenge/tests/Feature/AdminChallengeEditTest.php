<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Challenge;
use App\Models\Claim;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminChallengeEditTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Membuat akun admin untuk pengujian.
     */
    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);
    }

    /**
     * Membuat kategori challenge.
     */
    private function createCategory(): Category
    {
        return Category::create([
            'name' => 'Transportasi',
        ]);
    }

    /**
     * Membuat data challenge awal.
     */
    private function createChallenge(Category $category): Challenge
    {
        return Challenge::create([
            'category_id' => $category->id,
            'title' => 'Gunakan Transportasi Umum',
            'description' => 'Menggunakan transportasi umum untuk perjalanan sehari-hari.',
            'difficulty' => 'mudah',
            'points' => 20,
            'is_active' => true,
            'proof_criteria' => 'Foto saat menggunakan transportasi umum.',
            'estimated_co2e_gram' => 500,
            'emission_source' => 'Sumber perhitungan emisi',
        ]);
    }

    /**
     * Menyiapkan data valid untuk proses edit.
     */
    private function validUpdateData(Category $category): array
    {
        return [
            'category_id' => $category->id,
            'title' => 'Gunakan Transportasi Umum Setiap Hari',
            'description' => 'Menggunakan bus atau kereta untuk aktivitas sehari-hari.',
            'difficulty' => 'sedang',
            'points' => 30,
            'proof_criteria' => 'Foto saat menggunakan bus atau kereta.',
            'estimated_co2e_gram' => 700,
            'emission_source' => 'Referensi perhitungan emisi transportasi',
        ];
    }

    /**
     * TC-10-01: Admin dapat mengubah data challenge.
     */
    public function test_admin_dapat_mengubah_data_challenge(): void
    {
        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $challenge = $this->createChallenge($category);

        $data = $this->validUpdateData($category);

        $this->actingAs($admin)
            ->put(route('admin.challenges.update', $challenge), $data)
            ->assertRedirect(route('admin.challenges.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('challenges', [
            'id' => $challenge->id,
            'title' => 'Gunakan Transportasi Umum Setiap Hari',
            'description' => 'Menggunakan bus atau kereta untuk aktivitas sehari-hari.',
            'difficulty' => 'sedang',
            'points' => 30,
            'proof_criteria' => 'Foto saat menggunakan bus atau kereta.',
            'estimated_co2e_gram' => 700,
        ]);
    }

    /**
     * TC-10-02: Perubahan poin challenge tidak mengubah poin klaim lama.
     */
    public function test_perubahan_poin_challenge_tidak_mengubah_poin_klaim_lama(): void
    {
        $admin = $this->createAdmin();

        $mahasiswa = User::factory()->create([
            'role' => User::ROLE_MAHASISWA,
        ]);

        $category = $this->createCategory();
        $challenge = $this->createChallenge($category);

        $claim = Claim::create([
            'user_id' => $mahasiswa->id,
            'challenge_id' => $challenge->id,
            'claim_date' => now()->toDateString(),
            'status' => 'approved',
            'points_awarded' => 20,
            'co2e_awarded_gram' => 500,
        ]);

        $data = $this->validUpdateData($category);

        $this->actingAs($admin)
            ->put(route('admin.challenges.update', $challenge), $data)
            ->assertRedirect(route('admin.challenges.index'));

        $this->assertDatabaseHas('challenges', [
            'id' => $challenge->id,
            'points' => 30,
        ]);

        $this->assertDatabaseHas('claims', [
            'id' => $claim->id,
            'points_awarded' => 20,
            'co2e_awarded_gram' => 500,
        ]);
    }

    /**
     * TC-10-03: Perubahan challenge ditolak jika poin nol.
     */
    public function test_perubahan_challenge_ditolak_jika_poin_nol(): void
    {
        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $challenge = $this->createChallenge($category);

        $data = $this->validUpdateData($category);
        $data['points'] = 0;

        $this->actingAs($admin)
            ->from(route('admin.challenges.edit', $challenge))
            ->put(route('admin.challenges.update', $challenge), $data)
            ->assertRedirect(route('admin.challenges.edit', $challenge))
            ->assertSessionHasErrors('points');

        $this->assertDatabaseHas('challenges', [
            'id' => $challenge->id,
            'points' => 20,
        ]);
    }

    /**
     * TC-10-04: Perubahan challenge ditolak jika poin negatif.
     */
    public function test_perubahan_challenge_ditolak_jika_poin_negatif(): void
    {
        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $challenge = $this->createChallenge($category);

        $data = $this->validUpdateData($category);
        $data['points'] = -10;

        $this->actingAs($admin)
            ->from(route('admin.challenges.edit', $challenge))
            ->put(route('admin.challenges.update', $challenge), $data)
            ->assertRedirect(route('admin.challenges.edit', $challenge))
            ->assertSessionHasErrors('points');

        $this->assertDatabaseHas('challenges', [
            'id' => $challenge->id,
            'points' => 20,
        ]);
    }

    /**
     * TC-10-05: Admin dapat membuka form edit dengan data challenge saat ini.
     */
    public function test_admin_dapat_membuka_form_edit_challenge(): void
    {
        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $challenge = $this->createChallenge($category);

        $this->actingAs($admin)
            ->get(route('admin.challenges.edit', $challenge))
            ->assertOk()
            ->assertSee('Edit Challenge')
            ->assertSee('Gunakan Transportasi Umum')
            ->assertSee('Foto saat menggunakan transportasi umum.');
    }
}
