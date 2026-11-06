<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Challenge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminChallengeTest extends TestCase
{
    use RefreshDatabase;

    private function createChallenge(Category $category, array $override = []): Challenge
    {
        return Challenge::create(array_merge([
            'category_id' => $category->id,
            'created_by' => User::factory()->create()->id,
            'title' => 'Bawa tumbler',
            'description' => 'Menggunakan tumbler sendiri',
            'difficulty' => 'Mudah',
            'points' => 20,
            'is_active' => true,
            'proof_criteria' => 'Foto tumbler',
            'estimated_co2e_gram' => 83,
            'emission_source' => 'Contoh sumber emisi',
        ], $override));
    }

    public function test_admin_dapat_melihat_challenge_aktif_dan_nonaktif(): void
    {
        $category = Category::create(['name' => 'Energi']);

        $this->createChallenge($category, [
            'title' => 'Challenge Aktif',
        ]);

        $this->createChallenge($category, [
            'title' => 'Challenge Nonaktif',
            'is_active' => false,
        ]);

        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.challenges.index'))
            ->assertOk()
            ->assertSee('Challenge Aktif')
            ->assertSee('Challenge Nonaktif');
    }

    public function test_admin_dapat_memfilter_challenge_aktif(): void
    {
        $category = Category::create(['name' => 'Energi']);

        $this->createChallenge($category, [
            'title' => 'Challenge Aktif',
        ]);

        $this->createChallenge($category, [
            'title' => 'Challenge Nonaktif',
            'is_active' => false,
        ]);

        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.challenges.index', ['status' => 'active']))
            ->assertOk()
            ->assertSee('Challenge Aktif')
            ->assertDontSee('Challenge Nonaktif');
    }

    public function test_admin_dapat_memfilter_challenge_nonaktif(): void
    {
        $category = Category::create(['name' => 'Energi']);

        $this->createChallenge($category, [
            'title' => 'Challenge Aktif',
        ]);

        $this->createChallenge($category, [
            'title' => 'Challenge Nonaktif',
            'is_active' => false,
        ]);

        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.challenges.index', ['status' => 'inactive']))
            ->assertOk()
            ->assertSee('Challenge Nonaktif')
            ->assertDontSee('Challenge Aktif');
    }

    public function test_mahasiswa_tidak_dapat_mengakses_halaman_admin(): void
    {
        $mahasiswa = User::factory()->create([
            'role' => User::ROLE_MAHASISWA,
        ]);

        $this->actingAs($mahasiswa)
            ->get(route('admin.challenges.index'))
            ->assertForbidden();
    }

    public function test_pengguna_belum_login_diarahkan_ke_login(): void
    {
        $this->get(route('admin.challenges.index'))
            ->assertRedirect('/login');
    }
}