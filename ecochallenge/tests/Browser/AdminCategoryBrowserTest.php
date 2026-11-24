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

    private function loginViaForm(Browser $browser, string $email, string $password): Browser
    {
        $browser->logout();
        $browser->driver->manage()->deleteAllCookies();

        return $browser->visit('/login')
            ->waitForText('Masuk.', 15)
            ->pause(800)
            ->type('email', $email)
            ->pause(800)
            ->type('password', $password)
            ->pause(800)
            ->press('Masuk');
    }

    /** PBI-05 Normal: Admin login lewat form, buka Kelola Kategori, melihat daftar & jumlah challenge. */
    public function test_pbi05_admin_melihat_nama_dan_jumlah_challenge_tiap_kategori(): void
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

        $this->browse(function (Browser $browser) {
            $this->loginViaForm($browser, 'admin@ecochallenge.test', 'Admin123!')
                ->waitForLocation('/admin', 15)
                ->waitForText('DASHBOARD ADMIN', 15)
                ->pause(1500)
                ->clickLink('Kelola kategori')
                ->waitForLocation('/admin/categories', 15)
                ->waitForText('Kelola kategori', 15)
                ->assertSee('Transportasi')
                ->assertSee('1')
                ->pause(3000);
        });
    }

    /** PBI-05 Eksepsi: Mahasiswa login lewat form lalu mencoba buka /admin/categories ditolak 403. */
    public function test_pbi05_mahasiswa_membuka_url_kelola_kategori_menerima_403(): void
    {
        User::create([
            'name' => 'Mahasiswa Uji',
            'username' => 'mahasiswa.uji',
            'email' => 'mahasiswa@ecochallenge.test',
            'password' => bcrypt('Mahasiswa123!'),
            'role' => 'mahasiswa',
            'is_active' => true,
            'total_points' => 0,
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginViaForm($browser, 'mahasiswa@ecochallenge.test', 'Mahasiswa123!')
                ->waitForLocation('/dashboard', 15)
                ->pause(1200)
                ->visit('/admin/categories')
                ->assertSee('403')
                ->pause(3000);
        });
    }

    /** PBI-06 Normal & Eksepsi: Validasi kosong/duplikat serta tambah kategori baru. */
    public function test_pbi06_admin_dapat_menambah_kategori_baru_dan_melihat_validasi(): void
    {
        User::create([
            'name' => 'Admin EcoChallenge',
            'username' => 'admin',
            'email' => 'admin@ecochallenge.test',
            'password' => bcrypt('Admin123!'),
            'role' => 'admin',
            'is_active' => true,
            'total_points' => 0,
        ]);

        $this->browse(function (Browser $browser) {
            $this->loginViaForm($browser, 'admin@ecochallenge.test', 'Admin123!')
                ->waitForLocation('/admin', 15)
                ->pause(1000)
                ->clickLink('Kelola kategori')
                ->waitForLocation('/admin/categories', 15)
                ->pause(1200)
                ->press('+ Tambah Kategori')
                ->assertSee('Nama kategori wajib diisi.')
                ->pause(2000)
                ->type('name', 'Konservasi Air')
                ->type('description', 'Aktivitas menghemat air')
                ->pause(1200)
                ->press('+ Tambah Kategori')
                ->assertSee('Kategori baru berhasil ditambahkan.')
                ->assertSee('Konservasi Air')
                ->pause(2000)
                ->type('name', 'Konservasi Air')
                ->pause(1200)
                ->press('+ Tambah Kategori')
                ->assertSee('Nama kategori sudah terdaftar.')
                ->pause(3000);
        });
    }

    /** PBI-07 Normal & Eksepsi: Proteksi hapus kategori ber-challenge, ubah lewat modal, dan hapus kategori kosong. */
    public function test_pbi07_admin_dapat_mengubah_dan_menghapus_kategori_yang_belum_dipakai(): void
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

        $this->browse(function (Browser $browser) use ($kategoriKosong) {
            $this->loginViaForm($browser, 'admin@ecochallenge.test', 'Admin123!')
                ->waitForLocation('/admin', 15)
                ->pause(1000)
                ->clickLink('Kelola kategori')
                ->waitForLocation('/admin/categories', 15)
                ->assertPresent('button[disabled]')
                ->pause(2000)
                ->click('button[data-id="' . $kategoriKosong->id . '"]')
                ->whenAvailable('#editCategoryModal', function (Browser $modal) {
                    $modal->pause(1200)
                        ->clear('#edit_name')
                        ->type('#edit_name', 'Kategori Baru')
                        ->clear('#edit_description')
                        ->type('#edit_description', 'Deskripsi diperbarui')
                        ->pause(1200)
                        ->press('Simpan Perubahan');
                })
                ->waitForText('Kategori berhasil diperbarui.', 15)
                ->assertSee('Kategori Baru')
                ->pause(2000)
                ->click('form[action$="/admin/categories/' . $kategoriKosong->id . '"] button[type="submit"]')
                ->pause(1000)
                ->acceptDialog()
                ->waitForText('Kategori berhasil dihapus.', 15)
                ->assertDontSee('Kategori Baru')
                ->pause(3000);
        });
    }
}