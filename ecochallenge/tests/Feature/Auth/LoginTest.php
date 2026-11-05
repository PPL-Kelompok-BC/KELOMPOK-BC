<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function mahasiswa(array $override = []): User
    {
        return User::factory()->create(array_merge(['username' => 'mhs', 'email' => 'mhs@example.com'], $override));
    }

    private function admin(): User
    {
        return User::factory()->create(['username' => 'adm', 'email' => 'admin@example.com', 'role' => 'admin']);
    }

    public function test_halaman_login_bisa_dibuka(): void
    {
        $this->get('/login')->assertOk()->assertSee('Masuk.');
    }

    public function test_login_mahasiswa_membuka_dashboard_mahasiswa(): void
    {
        $this->mahasiswa();

        $this->post('/login', ['email' => 'mhs@example.com', 'password' => 'password'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->get(route('dashboard'))->assertOk()->assertSee('DASHBOARD MAHASISWA');
    }

    public function test_login_admin_membuka_dashboard_admin(): void
    {
        $this->admin();

        $this->post('/login', ['email' => 'admin@example.com', 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard'))->assertOk()->assertSee('DASHBOARD ADMIN');
    }

    public function test_email_tidak_membedakan_huruf_besar(): void
    {
        $this->mahasiswa();

        $this->post('/login', ['email' => 'MHS@Example.com', 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
    }

    public function test_kredensial_salah_menampilkan_pesan_dan_sisa_percobaan(): void
    {
        $this->mahasiswa();

        $this->from('/login')
            ->post('/login', ['email' => 'mhs@example.com', 'password' => 'salah'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'Email atau kata sandi salah. Sisa percobaan: 4.']);

        $this->assertGuest();
    }

    public function test_percobaan_keenam_setelah_lima_kali_gagal_ditahan_sementara(): void
    {
        $this->mahasiswa();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'mhs@example.com', 'password' => 'salah']);
        }

        // Kata sandi benar pun ditolak selama penahanan.
        $response = $this->post('/login', ['email' => 'mhs@example.com', 'password' => 'password']);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'Terlalu banyak percobaan masuk',
            session('errors')->first('email')
        );
        $this->assertGuest();
    }

    public function test_login_berhasil_menghapus_hitungan_gagal(): void
    {
        $this->mahasiswa();

        for ($i = 0; $i < 3; $i++) {
            $this->post('/login', ['email' => 'mhs@example.com', 'password' => 'salah']);
        }
        $this->post('/login', ['email' => 'mhs@example.com', 'password' => 'password'])->assertRedirect();
        $this->post('/logout');

        $this->from('/login')
            ->post('/login', ['email' => 'mhs@example.com', 'password' => 'salah'])
            ->assertSessionHasErrors(['email' => 'Email atau kata sandi salah. Sisa percobaan: 4.']);
    }

    public function test_akun_nonaktif_tidak_bisa_masuk(): void
    {
        $this->mahasiswa(['is_active' => false]);

        $this->from('/login')
            ->post('/login', ['email' => 'mhs@example.com', 'password' => 'password'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'Akun ini dinonaktifkan. Hubungi admin.']);

        $this->assertGuest();
    }

    public function test_logout_mengakhiri_sesi(): void
    {
        $this->actingAs($this->mahasiswa())
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_mahasiswa_tidak_bisa_membuka_halaman_admin(): void
    {
        $this->actingAs($this->mahasiswa())->get('/admin')->assertForbidden();
    }

    public function test_tamu_yang_membuka_halaman_admin_diarahkan_ke_login(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_admin_yang_membuka_dashboard_mahasiswa_diarahkan_ke_dashboard_admin(): void
    {
        $this->actingAs($this->admin())
            ->get('/dashboard')
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_pengguna_yang_sudah_masuk_diarahkan_dari_halaman_login_sesuai_peran(): void
    {
        $this->actingAs($this->admin())->get('/login')->assertRedirect(route('admin.dashboard'));
        $this->actingAs($this->mahasiswa())->get('/login')->assertRedirect(route('dashboard'));
    }

    public function test_halaman_tujuan_semula_dibuka_setelah_login(): void
    {
        $this->mahasiswa();

        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->post('/login', ['email' => 'mhs@example.com', 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
    }
}
