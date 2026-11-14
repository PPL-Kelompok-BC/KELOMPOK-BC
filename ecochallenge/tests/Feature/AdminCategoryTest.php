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

    private function createAdmin(): User
    {
        return User::create([
            'name' => 'Admin EcoChallenge',
            'username' => 'admin',
            'email' => 'admin@ecochallenge.test',
            'password' => bcrypt('Admin123!'),
            'role' => 'admin',
            'is_active' => true,
            'total_points' => 0,
        ]);
    }

    private function createMahasiswa(): User
    {
        return User::create([
            'name' => 'Mahasiswa Uji',
            'username' => 'mahasiswa.uji',
            'email' => 'mahasiswa@ecochallenge.test',
            'password' => bcrypt('Mahasiswa123!'),
            'role' => 'mahasiswa',
            'is_active' => true,
            'total_points' => 0,
        ]);
    }

    public function test_admin_dapat_melihat_daftar_kategori_dan_jumlah_challenge(): void
    {
        $admin = $this->createAdmin();

        $energi = Category::create([
            'name' => 'Energi',
            'description' => 'Hemat energi',
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'Transportasi',
            'description' => 'Transportasi hijau',
            'is_active' => true,
        ]);

        Challenge::create([
            'category_id' => $energi->id,
            'created_by' => $admin->id,
            'title' => 'Matikan Lampu Siang Hari',
            'description' => 'Hemat listrik',
            'difficulty' => 'mudah',
            'points' => 15,
            'is_active' => true,
            'proof_criteria' => 'Foto saklar lampu',
            'estimated_co2e_gram' => 250,
            'emission_source' => 'PLN',
        ]);

        $response = $this->actingAs($admin)->get('/admin/categories');

        $response->assertStatus(200);
        $response->assertSee('Kelola kategori');
        $response->assertSee('Energi');
        $response->assertSee('Transportasi');
    }

    public function test_mahasiswa_mengakses_kelola_kategori_menerima_403(): void
    {
        $mahasiswa = $this->createMahasiswa();

        $response = $this->actingAs($mahasiswa)->get('/admin/categories');

        $response->assertStatus(403);
    }

    public function test_admin_dapat_menambahkan_kategori_baru(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post('/admin/categories', [
            'name' => 'Konservasi Air',
            'description' => 'Aktivitas menghemat penggunaan air bersih',
        ]);

        $response->assertRedirect('/admin/categories');
        $response->assertSessionHas('success', 'Kategori baru berhasil ditambahkan.');
        $this->assertDatabaseHas('categories', [
            'name' => 'Konservasi Air',
            'description' => 'Aktivitas menghemat penggunaan air bersih',
            'is_active' => true,
        ]);
    }

    public function test_tambah_kategori_gagal_jika_nama_kosong_atau_duplikat(): void
    {
        $admin = $this->createAdmin();

        Category::create([
            'name' => 'Energi',
            'description' => 'Hemat energi',
            'is_active' => true,
        ]);

        $emptyResponse = $this->actingAs($admin)->post('/admin/categories', [
            'name' => '',
            'description' => 'Tanpa nama',
        ]);
        $emptyResponse->assertSessionHasErrors('name');

        $duplicateResponse = $this->actingAs($admin)->post('/admin/categories', [
            'name' => 'Energi',
            'description' => 'Duplikat energi',
        ]);
        $duplicateResponse->assertSessionHasErrors('name');
    }

    public function test_mahasiswa_tidak_dapat_menambahkan_kategori_dan_menerima_403(): void
    {
        $mahasiswa = $this->createMahasiswa();

        $response = $this->actingAs($mahasiswa)->post('/admin/categories', [
            'name' => 'Kategori Ilegal',
            'description' => 'Percobaan mahasiswa',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('categories', [
            'name' => 'Kategori Ilegal',
        ]);
    }

    public function test_admin_dapat_mengubah_kategori(): void
    {
        $admin = $this->createAdmin();

        $category = Category::create([
            'name' => 'Kategori Lama',
            'description' => 'Deskripsi lama',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put("/admin/categories/{$category->id}", [
            'name' => 'Kategori Diperbarui',
            'description' => 'Deskripsi baru',
        ]);

        $response->assertRedirect('/admin/categories');
        $response->assertSessionHas('success', 'Kategori berhasil diperbarui.');
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Kategori Diperbarui',
            'description' => 'Deskripsi baru',
        ]);
    }

    public function test_ubah_kategori_gagal_jika_nama_kosong_atau_duplikat(): void
    {
        $admin = $this->createAdmin();

        Category::create([
            'name' => 'Transportasi',
            'description' => 'Transportasi hijau',
            'is_active' => true,
        ]);

        $energi = Category::create([
            'name' => 'Energi',
            'description' => 'Hemat energi',
            'is_active' => true,
        ]);

        $emptyResponse = $this->actingAs($admin)->put("/admin/categories/{$energi->id}", [
            'name' => '',
            'description' => 'Deskripsi',
        ]);
        $emptyResponse->assertSessionHasErrors('name');

        $duplicateResponse = $this->actingAs($admin)->put("/admin/categories/{$energi->id}", [
            'name' => 'Transportasi',
            'description' => 'Duplikat',
        ]);
        $duplicateResponse->assertSessionHasErrors('name');

        $sameNameResponse = $this->actingAs($admin)->put("/admin/categories/{$energi->id}", [
            'name' => 'Energi',
            'description' => 'Deskripsi diperbarui',
        ]);
        $sameNameResponse->assertRedirect('/admin/categories');
        $sameNameResponse->assertSessionHasNoErrors();
    }

    public function test_admin_dapat_menghapus_kategori_yang_belum_dipakai(): void
    {
        $admin = $this->createAdmin();

        $category = Category::create([
            'name' => 'Kategori Kosong',
            'description' => 'Belum ada challenge',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete("/admin/categories/{$category->id}");

        $response->assertRedirect('/admin/categories');
        $response->assertSessionHas('success', 'Kategori berhasil dihapus.');
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_kategori_yang_masih_memiliki_challenge_tidak_dapat_dihapus(): void
    {
        $admin = $this->createAdmin();

        $category = Category::create([
            'name' => 'Energi',
            'description' => 'Hemat energi',
            'is_active' => true,
        ]);

        Challenge::create([
            'category_id' => $category->id,
            'created_by' => $admin->id,
            'title' => 'Hemat Listrik',
            'description' => 'Matikan perangkat tidak terpakai',
            'difficulty' => 'mudah',
            'points' => 10,
            'is_active' => true,
            'proof_criteria' => 'Foto stopkontak',
            'estimated_co2e_gram' => 200,
            'emission_source' => 'PLN',
        ]);

        $response = $this->actingAs($admin)->delete("/admin/categories/{$category->id}");

        $response->assertRedirect('/admin/categories');
        $response->assertSessionHas('error', 'Kategori yang masih memiliki challenge tidak dapat dihapus.');
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Energi',
        ]);
    }

    public function test_mahasiswa_tidak_dapat_mengubah_atau_menghapus_kategori_dan_menerima_403(): void
    {
        $mahasiswa = $this->createMahasiswa();

        $category = Category::create([
            'name' => 'Energi',
            'description' => 'Hemat energi',
            'is_active' => true,
        ]);

        $updateResponse = $this->actingAs($mahasiswa)->put("/admin/categories/{$category->id}", [
            'name' => 'Diubah Mahasiswa',
        ]);
        $updateResponse->assertStatus(403);

        $deleteResponse = $this->actingAs($mahasiswa)->delete("/admin/categories/{$category->id}");
        $deleteResponse->assertStatus(403);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Energi',
        ]);
    }
}