<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Challenge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminChallengeCreateTest extends TestCase
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
     * Data challenge valid untuk pengujian.
     */
    private function validChallengeData(Category $category): array
    {
        return [
            'category_id' => $category->id,
            'title' => 'Gunakan Transportasi Umum',
            'description' => 'Menggunakan transportasi umum untuk perjalanan sehari-hari',
            'difficulty' => 'mudah',
            'points' => 20,
            'proof_criteria' => 'Foto saat menggunakan transportasi umum',
            'estimated_co2e_gram' => 500,
            'emission_source' => 'Sumber perhitungan sesuai referensi yang digunakan',
            'is_active' => 1,
        ];
    }

    /**
     * TC-09-01: Admin dapat membuka form tambah challenge.
     */
    public function test_admin_dapat_membuka_form_tambah_challenge(): void
    {
        $admin = $this->createAdmin();

        Category::create([
            'name' => 'Transportasi',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.challenges.create'))
            ->assertOk()
            ->assertSee('Tambah Challenge')
            ->assertSee('Transportasi');
    }

    /**
     * TC-09-02: Admin dapat menyimpan challenge baru.
     */
    public function test_admin_dapat_menyimpan_challenge_baru(): void
    {
        $admin = $this->createAdmin();

        $category = Category::create([
            'name' => 'Transportasi',
        ]);

        $data = $this->validChallengeData($category);

        $this->actingAs($admin)
            ->post(route('admin.challenges.store'), $data)
            ->assertRedirect(route('admin.challenges.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('challenges', [
            'title' => 'Gunakan Transportasi Umum',
            'category_id' => $category->id,
            'created_by' => $admin->id,
            'proof_criteria' => 'Foto saat menggunakan transportasi umum',
            'estimated_co2e_gram' => 500,
            'is_active' => 1,
        ]);
    }

    /**
     * TC-09-03: Challenge ditolak jika proof criteria kosong.
     */
    public function test_challenge_ditolak_jika_proof_criteria_kosong(): void
    {
        $admin = $this->createAdmin();

        $category = Category::create([
            'name' => 'Transportasi',
        ]);

        $data = $this->validChallengeData($category);
        $data['proof_criteria'] = '';

        $this->actingAs($admin)
            ->from(route('admin.challenges.create'))
            ->post(route('admin.challenges.store'), $data)
            ->assertRedirect(route('admin.challenges.create'))
            ->assertSessionHasErrors('proof_criteria');

        $this->assertDatabaseMissing('challenges', [
            'title' => 'Gunakan Transportasi Umum',
        ]);
    }

    /**
     * TC-09-04: Challenge ditolak jika estimasi emisi negatif.
     */
    public function test_challenge_ditolak_jika_estimasi_emisi_negatif(): void
    {
        $admin = $this->createAdmin();

        $category = Category::create([
            'name' => 'Transportasi',
        ]);

        $data = $this->validChallengeData($category);
        $data['estimated_co2e_gram'] = -10;

        $this->actingAs($admin)
            ->from(route('admin.challenges.create'))
            ->post(route('admin.challenges.store'), $data)
            ->assertRedirect(route('admin.challenges.create'))
            ->assertSessionHasErrors('estimated_co2e_gram');

        $this->assertDatabaseMissing('challenges', [
            'title' => 'Gunakan Transportasi Umum',
        ]);
    }

    /**
     * TC-09-05: Challenge aktif yang dibuat admin muncul di katalog mahasiswa.
     */
    public function test_challenge_aktif_yang_dibuat_admin_muncul_di_katalog(): void
    {
        $admin = $this->createAdmin();

        $mahasiswa = User::factory()->create([
            'role' => User::ROLE_MAHASISWA,
        ]);

        $category = Category::create([
            'name' => 'Transportasi',
        ]);

        $data = $this->validChallengeData($category);

        $this->actingAs($admin)
            ->post(route('admin.challenges.store'), $data)
            ->assertRedirect(route('admin.challenges.index'));

        $this->actingAs($mahasiswa)
            ->get(route('challenges.index'))
            ->assertOk()
            ->assertSee('Gunakan Transportasi Umum');
    }
}
