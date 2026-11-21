<?php

namespace Tests\Browser\Fitur\Jonathan;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Tests\Browser\Fitur\FiturTestCase;

/**
 * Jonathan – melengkapi test case Jira PBI-03 (TC-01 s.d. TC-04) dan PBI-04 (TC-05 s.d. TC-12)
 * yang belum tercakup di Jonathan_PBI03_RegistrasiTest dan Jonathan_PBI04_LoginTest.
 */
class Jonathan_PBI03_PBI04_KasusEksepsiJiraTest extends FiturTestCase
{
    /** PBI-03 TC-01: akun tersimpan dengan kata sandi ter-hash dan streak awal bernilai 0. */
    public function test_pbi03_tc01_akun_tersimpan_dengan_kata_sandi_ter_hash_dan_streak_awal_nol(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')->waitForText('Buat akunmu.')
                ->type('name', 'Rani Hijau')->type('username', 'rani.hash')
                ->type('email', 'rani.hash@student.telkomuniversity.ac.id')
                ->type('password', self::KATA_SANDI)->type('password_confirmation', self::KATA_SANDI);
            $this->jeda($browser);
            $browser->press('Daftar')->waitForLocation('/dashboard')->waitForText('DASHBOARD MAHASISWA');
            $this->jeda($browser, 1000);
        });

        $user = User::where('username', 'rani.hash')->firstOrFail();
        $this->assertNotSame(self::KATA_SANDI, $user->password, 'Kata sandi tidak boleh tersimpan polos.');
        $this->assertTrue(Hash::check(self::KATA_SANDI, $user->password));
        $this->assertSame('mahasiswa', $user->role);
        $this->assertSame(0, (int) $user->total_points);
    }

    /**
     * PBI-03 TC-01 (bagian streak): sesuai UC-01 langkah 5 di proposal, registrasi membuat baris
     * streak awal bernilai 0 bersamaan dengan akun.
     */
    public function test_pbi03_tc01_baris_streak_awal_nol_dibuat_saat_registrasi(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')->waitForText('Buat akunmu.')
                ->type('name', 'Rani Streak')->type('username', 'rani.streak')
                ->type('email', 'rani.streak@student.telkomuniversity.ac.id')
                ->type('password', self::KATA_SANDI)->type('password_confirmation', self::KATA_SANDI);
            $this->jeda($browser);
            $browser->press('Daftar')->waitForLocation('/dashboard')->waitForText('DASHBOARD MAHASISWA');
        });

        $user = User::where('username', 'rani.streak')->firstOrFail();
        $baris = DB::table('streaks')->where('user_id', $user->id)->first();

        $this->assertNotNull($baris, 'Baris streak awal harus dibuat saat registrasi (UC-01 langkah 5).');
        $this->assertSame(0, (int) $baris->current_streak);
        $this->assertSame(0, (int) $baris->longest_streak);
    }

    /** PBI-03 TC-04: kata sandi 5 karakter ditolak. */
    public function test_pbi03_tc04_kata_sandi_kurang_dari_8_karakter_ditolak(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')->waitForText('Buat akunmu.')
                ->type('name', 'Rani')->type('username', 'rani.pendek')
                ->type('email', 'pendek@student.telkomuniversity.ac.id')
                ->type('password', 'abc12')->type('password_confirmation', 'abc12');
            $this->jeda($browser);
            $browser->press('Daftar')->waitFor('#password-error')
                ->assertSee('Kata sandi minimal 8 karakter.');
            $this->jeda($browser, 1000);
        });

        $this->assertDatabaseMissing('users', ['username' => 'rani.pendek']);
    }

    /** PBI-03 TC-04: konfirmasi kata sandi berbeda ditolak. */
    public function test_pbi03_tc04_konfirmasi_kata_sandi_berbeda_ditolak(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')->waitForText('Buat akunmu.')
                ->type('name', 'Rani')->type('username', 'rani.beda')
                ->type('email', 'beda@student.telkomuniversity.ac.id')
                ->type('password', self::KATA_SANDI)->type('password_confirmation', 'Berbeda12345!');
            $this->jeda($browser);
            $browser->press('Daftar')->waitFor('#password-error')
                ->assertSee('Konfirmasi kata sandi tidak sama.');
            $this->jeda($browser, 1000);
        });

        $this->assertDatabaseMissing('users', ['username' => 'rani.beda']);
    }

    /** PBI-04 TC-07: email yang tidak terdaftar mendapat pesan generik yang sama. */
    public function test_pbi04_tc07_email_tidak_terdaftar_mendapat_pesan_generik(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')->waitForText('Masuk.')
                ->type('email', 'tidak.ada@student.telkomuniversity.ac.id')
                ->type('password', 'apa-saja-12345');
            $this->jeda($browser);
            $browser->press('Masuk')->waitForText('Email atau kata sandi salah.')
                ->assertPathIs('/login');
            $this->jeda($browser, 1000);
        });
    }

    /** PBI-04 TC-09: akun nonaktif ditolak dengan pesan yang menyebut akun dinonaktifkan. */
    public function test_pbi04_tc09_akun_nonaktif_ditolak_dengan_pesan(): void
    {
        $user = $this->mahasiswa(['is_active' => false]);

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user);
            // Pesan sesuai UC-02 alur 2c di proposal.
            $browser->waitForText('dinonaktifkan')->assertSee('Akun Anda dinonaktifkan, hubungi admin')->assertPathIs('/login');
            $this->jeda($browser, 1200);
        });
    }

    /** PBI-04 TC-11: tamu yang membuka halaman terproteksi diarahkan ke login. */
    public function test_pbi04_tc11_tamu_membuka_halaman_terproteksi_diarahkan_ke_login(): void
    {
        $this->browse(function (Browser $browser) {
            foreach (['/dashboard', '/profil', '/leaderboard', '/claims', '/admin'] as $alamat) {
                $browser->visit($alamat)->waitForText('Masuk.')->assertPathIs('/login');
                $this->jeda($browser, 500);
            }
        });
    }
}
