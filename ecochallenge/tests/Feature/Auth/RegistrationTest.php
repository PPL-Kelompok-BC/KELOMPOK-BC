<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'name' => 'Jonathan Sharron',
            'username' => 'jonathan.s',
            'email' => 'jonathan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $override);
    }

    public function test_halaman_daftar_bisa_dibuka(): void
    {
        $this->get('/register')->assertOk()->assertSee('Buat akunmu.');
    }

    public function test_isian_lengkap_membuat_akun_mahasiswa_dan_membuka_dashboard(): void
    {
        $response = $this->post('/register', $this->payload());

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'username' => 'jonathan.s',
            'email' => 'jonathan@example.com',
            'role' => 'mahasiswa',
        ]);
        $this->assertNotSame('password123', User::first()->password);
        $this->get(route('dashboard'))->assertOk()->assertSee('Jonathan Sharron');
    }

    /** UC-01 langkah 5: registrasi membuat baris streak awal bernilai 0. */
    public function test_registrasi_membuat_baris_streak_awal_bernilai_nol(): void
    {
        $this->post('/register', $this->payload());

        $user = User::where('username', 'jonathan.s')->firstOrFail();
        $this->assertDatabaseHas('streaks', [
            'user_id' => $user->id,
            'current_streak' => 0,
            'longest_streak' => 0,
            'last_active_date' => null,
        ]);
    }

    /** Batasan PBI-03: registrasi tidak bisa membuat akun Admin (admin hanya dari seeder). */
    public function test_registrasi_tidak_bisa_membuat_akun_admin_walau_peran_dikirim(): void
    {
        $this->post('/register', $this->payload(['role' => 'admin', 'is_active' => false]))
            ->assertRedirect(route('dashboard'));

        $user = User::where('username', 'jonathan.s')->firstOrFail();
        $this->assertSame(User::ROLE_MAHASISWA, $user->role);
        $this->assertTrue((bool) $user->is_active);
        $this->get('/admin')->assertForbidden();
    }

    /** UC-01 alur 4a: isian lain tetap terisi kecuali kata sandi. */
    public function test_isian_lain_tetap_terisi_kecuali_kata_sandi_saat_ditolak(): void
    {
        User::factory()->create(['username' => 'lain', 'email' => 'jonathan@example.com']);

        $this->from('/register')->post('/register', $this->payload(['username' => 'baru']))
            ->assertSessionHasInput('name', 'Jonathan Sharron')
            ->assertSessionHasInput('username', 'baru')
            ->assertSessionHasInput('email', 'jonathan@example.com')
            ->assertSessionMissing('_old_input.password');

        $this->assertDatabaseCount('streaks', 0);
    }

    public function test_email_yang_sudah_dipakai_ditolak_dengan_pesan(): void
    {
        User::factory()->create(['username' => 'lain', 'email' => 'jonathan@example.com']);

        $this->from('/register')
            ->post('/register', $this->payload(['username' => 'baru']))
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['email' => 'Email sudah terdaftar. Gunakan email lain atau masuk.']);

        $this->assertGuest();
        $this->assertSame(1, User::count());
    }

    public function test_username_yang_sudah_dipakai_ditolak_tanpa_membedakan_huruf_besar(): void
    {
        User::factory()->create(['username' => 'jonathan.s', 'email' => 'lain@example.com']);

        $this->from('/register')
            ->post('/register', $this->payload(['username' => 'Jonathan.S']))
            ->assertRedirect('/register')
            ->assertSessionHasErrors(['username' => 'Username sudah dipakai. Pilih username lain.']);

        $this->assertGuest();
    }

    public function test_pesan_kesalahan_tampil_di_samping_isian(): void
    {
        User::factory()->create(['username' => 'jonathan.s', 'email' => 'lain@example.com']);

        $this->followingRedirects()
            ->from('/register')
            ->post('/register', $this->payload())
            ->assertSee('Username sudah dipakai. Pilih username lain.')
            ->assertSee('id="username-error"', false);
    }

    public function test_validasi_isian_wajib_dan_format(): void
    {
        $this->post('/register', $this->payload([
            'name' => '',
            'username' => 'a b',
            'email' => 'bukan-email',
            'password' => 'pendek',
            'password_confirmation' => 'beda',
        ]))->assertSessionHasErrors(['name', 'username', 'email', 'password']);

        $this->assertGuest();
    }

    public function test_pengguna_yang_sudah_login_tidak_bisa_membuka_halaman_daftar(): void
    {
        $this->actingAs(User::factory()->create(['username' => 'sudah']))
            ->get('/register')
            ->assertRedirect();
    }

    public function test_tamu_yang_membuka_dashboard_diarahkan_ke_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }
}
