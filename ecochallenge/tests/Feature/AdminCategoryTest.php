<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Challenge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dapat_melihat_daftar_kategori_dan_jumlah_challenge(): void
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

        $kategoriEnergi = Category::create([
            'name' => 'Energi',
            'description' => 'Aktivitas menghemat energi',
            'is_active' => true,
        ]);

        $kategoriSampah = Category::create([
            'name' => 'Pengelolaan Sampah',
            'description' => 'Aktivitas mengurangi sampah',
            'is_active' => true,
        ]);

        Challenge::create([
            'category_id' => $kategoriEnergi->id,
            'created_by' => $admin->id,
            'title' => 'Matikan Lampu',
            'description' => 'Matikan lampu saat keluar ruangan',
            'difficulty' => 'mudah',
            'points' => 10,
            'is_active' => true,
            'proof_criteria' => 'Foto saklar lampu mati',
            'estimated_co2e_gram' => 100,
            'emission_source' => 'Estimasi hemat listrik',
        ]);

        $response = $this->actingAs($admin)->get('/admin/categories');

        $response->assertStatus(200);
        $response->assertSee('Kelola kategori');
        $response->assertSee('Energi');
        $response->assertSee('1');
        $response->assertSee('Pengelolaan Sampah');
        $response->assertSee('0');
    }

    public function test_mahasiswa_mengakses_kelola_kategori_menerima_403(): void
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

        $response = $this->actingAs($mahasiswa)->get('/admin/categories');

        $response->assertStatus(403);
    }

    public function test_admin_dapat_menambahkan_kategori_baru(): void
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

        $response = $this->actingAs($admin)->post('/admin/categories', [
            'name' => 'Konservasi Air',
            'description' => 'Aktivitas menghemat penggunaan air',
        ]);

        $response->assertRedirect('/admin/categories');
        $response->assertSessionHas('success', 'Kategori baru berhasil ditambahkan.');
        $this->assertDatabaseHas('categories', [
            'name' => 'Konservasi Air',
            'description' => 'Aktivitas menghemat penggunaan air',
            'is_active' => true,
        ]);
    }

    public function test_tambah_kategori_gagal_jika_nama_kosong_atau_duplikat(): void
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

        Category::create([
            'name' => 'Energi',
            'description' => 'Aktivitas menghemat energi',
            'is_active' => true,
        ]);

        $responseKosong = $this->actingAs($admin)->post('/admin/categories', [
            'name' => '',
            'description' => 'Tanpa nama',
        ]);
        $responseKosong->assertSessionHasErrors('name');

        $responseDuplikat = $this->actingAs($admin)->post('/admin/categories', [
            'name' => 'Energi',
            'description' => 'Duplikat energi',
        ]);
        $responseDuplikat->assertSessionHasErrors('name');
    }

    public function test_mahasiswa_tidak_dapat_menambahkan_kategori_dan_menerima_403(): void
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

        $response = $this->actingAs($mahasiswa)->post('/admin/categories', [
            'name' => 'Kategori Ilegal',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('categories', [
            'name' => 'Kategori Ilegal',
        ]);
    }
}