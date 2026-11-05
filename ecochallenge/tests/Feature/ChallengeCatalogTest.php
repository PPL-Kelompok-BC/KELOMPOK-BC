<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Challenge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ChallengeCatalogTest extends TestCase
{
    use RefreshDatabase;

    private function challenge(Category $category, array $override = []): Challenge
    {
        return Challenge::create(array_merge([
            'category_id' => $category->id, 'created_by' => User::factory()->create()->id,
            'title' => 'Bawa tumbler', 'description' => 'Deskripsi', 'difficulty' => 'Mudah',
            'points' => 20, 'is_active' => true, 'proof_criteria' => 'Foto tumbler',
            'estimated_co2e_gram' => 83, 'emission_source' => 'Contoh',
        ], $override));
    }

    public function test_pbi12_katalog_menampilkan_challenge_aktif_dan_menyembunyikan_nonaktif(): void
    {
        $energi = Category::create(['name' => 'Energi']);
        $this->challenge($energi, ['title' => 'Aktif A']);
        $this->challenge($energi, ['title' => 'Nonaktif B', 'is_active' => false]);

        $this->actingAs(User::factory()->create())
            ->get('/challenges')
            ->assertOk()->assertSee('Aktif A')->assertDontSee('Nonaktif B');
    }

    public function test_pbi13_filter_kategori_dan_pesan_kosong(): void
    {
        $energi = Category::create(['name' => 'Energi']);
        $sampah = Category::create(['name' => 'Sampah']);
        $this->challenge($energi, ['title' => 'Hemat listrik']);

        $user = User::factory()->create();
        $this->actingAs($user)->get('/challenges?kategori='.$energi->id)->assertSee('Hemat listrik');
        $this->actingAs($user)->get('/challenges?kategori='.$sampah->id)
            ->assertSee('Belum ada challenge di kategori ini');
    }

    public function test_pbi14_detail_dan_404_untuk_challenge_nonaktif(): void
    {
        $energi = Category::create(['name' => 'Energi']);
        $aktif = $this->challenge($energi);
        $nonaktif = $this->challenge($energi, ['title' => 'Mati', 'is_active' => false]);

        $user = User::factory()->create();
        $this->actingAs($user)->get('/challenges/'.$aktif->id)->assertOk()->assertSee('Foto tumbler');
        $this->actingAs($user)->get('/challenges/'.$nonaktif->id)->assertNotFound();
    }

    public function test_pengguna_belum_login_diarahkan_ke_login(): void
    {
        if (! Route::has('login')) {
            $this->markTestSkipped('Menunggu autentikasi (login) dari Jonathan.');
        }

        $this->get('/challenges')->assertRedirect('/login');
    }
}
