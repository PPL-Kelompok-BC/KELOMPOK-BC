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

    // PBI-08: Admin dapat melihat challenge aktif dan nonaktif.
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

    // PBI-08: Admin dapat memfilter challenge aktif.
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

    // PBI-08: Admin dapat memfilter challenge nonaktif.
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

    // PBI-08: Mahasiswa tidak dapat mengakses halaman admin.
    public function test_mahasiswa_tidak_dapat_mengakses_halaman_admin(): void
    {
        $mahasiswa = User::factory()->create([
            'role' => User::ROLE_MAHASISWA,
        ]);

        $this->actingAs($mahasiswa)
            ->get(route('admin.challenges.index'))
            ->assertForbidden();
    }

    // PBI-08: Pengguna yang belum login diarahkan ke halaman login.
    public function test_pengguna_belum_login_diarahkan_ke_login(): void
    {
        $this->get(route('admin.challenges.index'))
            ->assertRedirect('/login');
    }

    // PBI-11: Admin dapat menonaktifkan challenge.
    public function test_admin_dapat_menonaktifkan_challenge(): void
    {
        $category = Category::create(['name' => 'Energi']);

        $challenge = $this->createChallenge($category);

        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.challenges.deactivate', $challenge))
            ->assertRedirect(route('admin.challenges.index'))
            ->assertSessionHas('success', 'Challenge berhasil dinonaktifkan.');

        $this->assertDatabaseHas('challenges', [
            'id' => $challenge->id,
            'is_active' => false,
        ]);
    }

    // PBI-11: Admin dapat mengaktifkan kembali challenge.
    public function test_admin_dapat_mengaktifkan_kembali_challenge(): void
    {
        $category = Category::create(['name' => 'Energi']);

        $challenge = $this->createChallenge($category, [
            'is_active' => false,
        ]);

        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.challenges.activate', $challenge))
            ->assertRedirect(route('admin.challenges.index'))
            ->assertSessionHas('success', 'Challenge berhasil diaktifkan kembali.');

        $this->assertDatabaseHas('challenges', [
            'id' => $challenge->id,
            'is_active' => true,
        ]);
    }

    // PBI-11: Mahasiswa tidak dapat menonaktifkan challenge.
    public function test_mahasiswa_tidak_dapat_menonaktifkan_challenge(): void
    {
        $category = Category::create(['name' => 'Energi']);

        $challenge = $this->createChallenge($category);

        $mahasiswa = User::factory()->create([
            'role' => User::ROLE_MAHASISWA,
        ]);

        $this->actingAs($mahasiswa)
            ->post(route('admin.challenges.deactivate', $challenge))
            ->assertForbidden();

        $this->assertDatabaseHas('challenges', [
            'id' => $challenge->id,
            'is_active' => true,
        ]);
    }

    // PBI-11: Pengguna yang belum login tidak dapat menonaktifkan challenge.
    public function test_pengguna_belum_login_tidak_dapat_menonaktifkan_challenge(): void
    {
        $category = Category::create(['name' => 'Energi']);

        $challenge = $this->createChallenge($category);

        $this->post(route('admin.challenges.deactivate', $challenge))
            ->assertRedirect('/login');

        $this->assertDatabaseHas('challenges', [
            'id' => $challenge->id,
            'is_active' => true,
        ]);
    }
}
