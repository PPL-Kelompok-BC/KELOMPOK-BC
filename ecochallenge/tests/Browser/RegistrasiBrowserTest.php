<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * PBI-03 · Jonathan – Registrasi akun mahasiswa (SCRUM-23).
 * Test case Jira TC-01 s.d. TC-04 (Laravel Dusk), mengikuti UC-01 Registrasi Akun di proposal.
 */
class RegistrasiBrowserTest extends DuskTestCase
{
    use DatabaseMigrations;

    private const KATA_SANDI = 'Rahasia123!';

    private function isiFormulir(Browser $browser, array $data): Browser
    {
        $data = array_merge([
            'name' => 'Rani Hijau',
            'username' => 'rani.hijau',
            'email' => 'rani@student.telkomuniversity.ac.id',
            'password' => self::KATA_SANDI,
            'password_confirmation' => self::KATA_SANDI,
        ], $data);

        // Buang sesi tes sebelumnya (Dusk memakai ulang jendela browser).
        $browser->logout();
        $browser->driver->manage()->deleteAllCookies();
        $browser->visit('/register')->waitForText('Buat akunmu.');

        foreach ($data as $kolom => $nilai) {
            $browser->type($kolom, $nilai);
        }

        return $browser;
    }

    /** TC-01 Normal: isian lengkap dan unik membuat akun Mahasiswa lalu membuka dashboard. */
    public function test_tc01_registrasi_valid_membuat_akun_mahasiswa_dan_membuka_dashboard(): void
    {
        $this->browse(function (Browser $browser) {
            $this->isiFormulir($browser, [])
                ->press('Daftar')
                ->waitForLocation('/dashboard')
                ->waitForText('DASHBOARD MAHASISWA')
                ->assertSee('Rani Hijau');
        });

        $user = User::where('username', 'rani.hijau')->firstOrFail();
        $this->assertSame(User::ROLE_MAHASISWA, $user->role);
        $this->assertTrue((bool) $user->is_active);
        $this->assertNotSame(self::KATA_SANDI, $user->password, 'Kata sandi harus tersimpan dalam bentuk hash.');
        $this->assertTrue(Hash::check(self::KATA_SANDI, $user->password));

        // UC-01 langkah 5: baris streak awal bernilai 0 ikut dibuat.
        $this->assertDatabaseHas('streaks', ['user_id' => $user->id, 'current_streak' => 0, 'longest_streak' => 0]);
    }

    /** TC-02 Eksepsi: email yang sudah dipakai ditolak dengan pesan di samping isian email. */
    public function test_tc02_email_yang_sudah_dipakai_ditolak(): void
    {
        User::factory()->create(['username' => 'pemilik.lama', 'email' => 'rani@student.telkomuniversity.ac.id']);

        $this->browse(function (Browser $browser) {
            $this->isiFormulir($browser, ['username' => 'rani.baru'])
                ->press('Daftar')
                ->waitFor('#email-error')
                ->assertSeeIn('#email-error', 'Email sudah terdaftar. Gunakan email lain atau masuk.')
                ->assertPathIs('/register')
                // UC-01 alur 4a: isian lain tetap terisi kecuali kata sandi.
                ->assertInputValue('name', 'Rani Hijau')
                ->assertInputValue('username', 'rani.baru')
                ->assertInputValue('email', 'rani@student.telkomuniversity.ac.id')
                ->assertInputValue('password', '');
        });

        $this->assertDatabaseMissing('users', ['username' => 'rani.baru']);
    }

    /** TC-03 Eksepsi: username yang sudah dipakai ditolak dengan pesan di samping isian username. */
    public function test_tc03_username_yang_sudah_dipakai_ditolak(): void
    {
        User::factory()->create(['username' => 'rani.hijau', 'email' => 'lama@student.telkomuniversity.ac.id']);

        $this->browse(function (Browser $browser) {
            $this->isiFormulir($browser, ['email' => 'baru@student.telkomuniversity.ac.id'])
                ->press('Daftar')
                ->waitFor('#username-error')
                ->assertSeeIn('#username-error', 'Username sudah dipakai. Pilih username lain.')
                ->assertPathIs('/register');
        });

        $this->assertDatabaseMissing('users', ['email' => 'baru@student.telkomuniversity.ac.id']);
    }

    /** TC-04 Eksepsi: kata sandi 5 karakter ditolak. */
    public function test_tc04_kata_sandi_kurang_dari_8_karakter_ditolak(): void
    {
        $this->browse(function (Browser $browser) {
            $this->isiFormulir($browser, ['password' => 'abc12', 'password_confirmation' => 'abc12'])
                ->press('Daftar')
                ->waitFor('#password-error')
                ->assertSeeIn('#password-error', 'Kata sandi minimal 8 karakter.');
        });

        $this->assertDatabaseMissing('users', ['username' => 'rani.hijau']);
    }

    /** TC-04 Eksepsi: konfirmasi kata sandi yang berbeda ditolak. */
    public function test_tc04_konfirmasi_kata_sandi_berbeda_ditolak(): void
    {
        $this->browse(function (Browser $browser) {
            $this->isiFormulir($browser, ['password_confirmation' => 'Berbeda12345!'])
                ->press('Daftar')
                ->waitFor('#password-error')
                ->assertSeeIn('#password-error', 'Konfirmasi kata sandi tidak sama.');
        });

        $this->assertDatabaseMissing('users', ['username' => 'rani.hijau']);
    }
}
