<?php

namespace Tests\Browser;

use App\Models\Category;
use App\Models\Challenge;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminCategoryBrowserTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_admin_melihat_nama_dan_jumlah_challenge_tiap_kategori(): void
    {
        $admin = User::create([
            'name' => 'Admin EcoChallenge',
            'username' => 'admin',
            'email' => 'admin@ecochallenge.test',
            'password' => bcrypt('Admin123!'),
            'role' => 'admin',
            'is_active' => true,
            'total_points' => 0,
        ]);

        $kategori = Category::create([
            'name' => 'Transportasi',
            'description' => 'Aktivitas transportasi ramah lingkungan',
            'is_active' => true,
        ]);

        Challenge::create([
            'category_id' => $kategori->id,
            'created_by' => $admin->id,
            'title' => 'Berjalan Kaki ke Kampus',
            'description' => 'Berjalan kaki jarak dekat',
            'difficulty' => 'mudah',
            'points' => 20,
            'is_active' => true,
            'proof_criteria' => 'Foto rute jalan kaki',
            'estimated_co2e_gram' => 500,
            'emission_source' => 'Estimasi kendaraan dihindari',
        ]);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/admin/categories')
                ->assertSee('Kelola kategori')
                ->assertSee('Transportasi')
                ->assertSee('1');
        });
    }

    public function test_mahasiswa_membuka_url_kelola_kategori_menerima_403(): void
    {
        $mahasiswa = User::create([
            'name' => 'Seto Satrio',
            'username' => 'seto.satrio',
            'email' => 'setosatrio@student.telkomuniversity.ac.id',
            'password' => bcrypt('Mahasiswa123!'),
            'role' => 'mahasiswa',
            'is_active' => true,
            'total_points' => 0,
        ]);

        $this->browse(function (Browser $browser) use ($mahasiswa) {
            $browser->loginAs($mahasiswa)
                ->visit('/admin/categories')
                ->assertSee('403');
        });
    }
}