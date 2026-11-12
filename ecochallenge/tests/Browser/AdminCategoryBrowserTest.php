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
            'name' => 'Mahasiswa Uji',
            'username' => 'mahasiswa.uji',
            'email' => 'mahasiswa@ecochallenge.test',
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

    public function test_admin_dapat_menambah_kategori_baru_dan_melihat_validasi(): void
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

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                ->visit('/admin/categories')
                ->press('+ Tambah Kategori')
                ->assertSee('Nama kategori wajib diisi.')
                ->type('name', 'Konservasi Air')
                ->type('description', 'Aktivitas menghemat air')
                ->press('+ Tambah Kategori')
                ->assertSee('Kategori baru berhasil ditambahkan.')
                ->assertSee('Konservasi Air')
                ->type('name', 'Konservasi Air')
                ->press('+ Tambah Kategori')
                ->assertSee('Nama kategori sudah terdaftar.');
        });
    }

    public function test_admin_dapat_mengubah_dan_menghapus_kategori_yang_belum_dipakai(): void
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

        $kategoriDipakai = Category::create([
            'name' => 'Transportasi',
            'description' => 'Transportasi ramah lingkungan',
            'is_active' => true,
        ]);

        Challenge::create([
            'category_id' => $kategoriDipakai->id,
            'created_by' => $admin->id,
            'title' => 'Naik Sepeda',
            'description' => 'Bersepeda ke kampus',
            'difficulty' => 'mudah',
            'points' => 20,
            'is_active' => true,
            'proof_criteria' => 'Foto sepeda',
            'estimated_co2e_gram' => 400,
            'emission_source' => 'Estimasi kendaraan',
        ]);

        $kategoriKosong = Category::create([
            'name' => 'Kategori Sementara',
            'description' => 'Deskripsi awal',
            'is_active' => true,
        ]);

        $this->browse(function (Browser $browser) use ($admin, $kategoriKosong) {
            $browser->loginAs($admin)
                ->visit('/admin/categories')
                ->assertPresent('button[disabled]')
                ->click('button[data-id="' . $kategoriKosong->id . '"]')
                ->whenAvailable('#editCategoryModal', function (Browser $modal) {
                    $modal->clear('#edit_name')
                        ->type('#edit_name', 'Kategori Baru')
                        ->clear('#edit_description')
                        ->type('#edit_description', 'Deskripsi diperbarui')
                        ->press('Simpan Perubahan');
                })
                ->waitForText('Kategori berhasil diperbarui.')
                ->assertSee('Kategori Baru')
                ->click('form[action$="/admin/categories/' . $kategoriKosong->id . '"] button[type="submit"]')
                ->acceptDialog()
                ->waitForText('Kategori berhasil dihapus.')
                ->assertDontSee('Kategori Baru');
        });
    }
}