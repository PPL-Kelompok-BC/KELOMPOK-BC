<?php

namespace Tests\Browser;

use App\Models\Category;
use App\Models\Challenge;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminChallengeEditBrowserTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_admin_dapat_mengedit_challenge_melalui_browser(): void
    {
        // Membuat akun admin.
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        // Membuat kategori.
        $category = Category::create([
            'name' => 'Transportasi',
        ]);

        // Membuat challenge awal.
        $challenge = Challenge::create([
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

        $this->browse(function (Browser $browser) use ($admin, $challenge) {
            $browser->loginAs($admin)
                ->visitRoute('admin.challenges.edit', $challenge)
                ->assertSee('Edit Challenge')
                ->assertInputValue('title', 'Gunakan Transportasi Umum')
                ->assertInputValue('points', '20')

                // Mengubah judul dan poin.
                ->clear('#title')
                ->type('#title', 'Gunakan Transportasi Umum Setiap Hari')
                ->clear('#points')
                ->type('#points', '35')

                // Menyimpan perubahan.
                ->press('Simpan Perubahan')
                ->assertPathIs('/admin/challenges')
                ->assertSee('Gunakan Transportasi Umum Setiap Hari')
                ->assertSee('35 poin');
        });
    }
}
