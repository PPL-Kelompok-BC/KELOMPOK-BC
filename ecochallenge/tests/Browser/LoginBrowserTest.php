<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * PBI-04 · Jonathan – Login, logout, dan pengalihan sesuai peran (SCRUM-24).
 * Test case Jira TC-05 s.d. TC-12 (Laravel Dusk).
 */
class LoginBrowserTest extends DuskTestCase
{
    use DatabaseMigrations;

    private const KATA_SANDI = 'Rahasia123!';

    private function akun(string $peran = User::ROLE_MAHASISWA, array $atribut = []): User
    {
        return User::factory()->create(array_merge([
            'name' => $peran === User::ROLE_ADMIN ? 'Admin EcoChallenge' : 'Rani Hijau',
            'username' => $peran === User::ROLE_ADMIN ? 'admin.uji' : 'rani.hijau',
            'email' => $peran === User::ROLE_ADMIN ? 'admin.uji@ecochallenge.test' : 'rani@student.telkomuniversity.ac.id',
            'password' => bcrypt(self::KATA_SANDI),
            'role' => $peran,
            'is_active' => true,
        ], $atribut));
    }

    /**
     * Isi formulir login sungguhan. Sesi lama dibuang dulu (keluar + hapus cookie), karena Dusk
     * memakai ulang jendela browser dan sesi bisa menyimpan "intended URL" dari tes sebelumnya.
     */
    private function masuk(Browser $browser, string $email, string $kataSandi = self::KATA_SANDI): Browser
    {
        $browser->logout();
        $browser->driver->manage()->deleteAllCookies();

        return $browser->visit('/login')
            ->waitForText('Masuk.')
            ->type('email', $email)
            ->type('password', $kataSandi)
            ->press('Masuk');
    }

    /** TC-05 Normal: login mahasiswa diarahkan ke dashboard mahasiswa. */
    public function test_tc05_login_mahasiswa_diarahkan_ke_dashboard_mahasiswa(): void
    {
        $user = $this->akun();

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user->email)
                ->waitForLocation('/dashboard')
                ->waitForText('DASHBOARD MAHASISWA')
                ->assertSee('Rani Hijau');
        });
    }

    /** TC-06 Normal: login admin diarahkan ke dashboard admin. */
    public function test_tc06_login_admin_diarahkan_ke_dashboard_admin(): void
    {
        $admin = $this->akun(User::ROLE_ADMIN);

        $this->browse(function (Browser $browser) use ($admin) {
            $this->masuk($browser, $admin->email)
                ->waitForLocation('/admin')
                ->waitForText('DASHBOARD ADMIN')
                ->assertSee('Admin EcoChallenge');
        });
    }

    /** TC-07 Eksepsi: kata sandi salah atau email tidak terdaftar memberi pesan generik yang sama. */
    public function test_tc07_kredensial_salah_tetap_di_login_dengan_pesan_generik(): void
    {
        $user = $this->akun();

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user->email, 'kata-sandi-salah')
                ->waitForText('Email atau kata sandi salah.')
                ->assertPathIs('/login');

            $this->masuk($browser, 'tidak.terdaftar@student.telkomuniversity.ac.id')
                ->waitForText('Email atau kata sandi salah.')
                ->assertPathIs('/login');
        });
    }

    /** TC-08 Eksepsi: percobaan keenam setelah lima kali gagal dalam satu menit ditahan sementara. */
    public function test_tc08_percobaan_keenam_setelah_lima_kali_gagal_ditahan(): void
    {
        $user = $this->akun(User::ROLE_MAHASISWA, ['email' => 'batas.'.uniqid().'@student.telkomuniversity.ac.id']);

        $this->browse(function (Browser $browser) use ($user) {
            for ($percobaan = 1; $percobaan <= 5; $percobaan++) {
                $this->masuk($browser, $user->email, 'salah-'.$percobaan)->waitForText('Email atau kata sandi salah.');
            }

            // Percobaan keenam, walaupun kata sandinya benar, tetap ditahan.
            $this->masuk($browser, $user->email)
                ->waitForText('detik')
                ->assertSee('Coba lagi dalam')
                ->assertPathIs('/login');
        });
    }

    /** TC-09 Eksepsi: akun nonaktif ditolak dengan pesan akun dinonaktifkan. */
    public function test_tc09_akun_nonaktif_ditolak(): void
    {
        $user = $this->akun(User::ROLE_MAHASISWA, ['is_active' => false]);

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user->email)
                ->waitForText('dinonaktifkan')
                ->assertSee('Hubungi admin')
                ->assertPathIs('/login');
        });
    }

    /** TC-10 Normal: logout menghapus sesi; halaman terproteksi kembali meminta login. */
    public function test_tc10_logout_menghapus_sesi(): void
    {
        $user = $this->akun();

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user->email)->waitForLocation('/dashboard');

            $browser->click('header button[aria-haspopup="menu"]')
                ->waitForText('Keluar')
                ->press('Keluar')
                ->waitForLocation('/login');

            $browser->visit('/dashboard')->waitForText('Masuk.')->assertPathIs('/login');
        });
    }

    /** TC-11 Eksepsi: tamu yang membuka halaman terproteksi diarahkan ke login. */
    public function test_tc11_tamu_membuka_halaman_terproteksi_diarahkan_ke_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->logout();
            $browser->driver->manage()->deleteAllCookies();

            foreach (['/dashboard', '/admin', '/leaderboard'] as $alamat) {
                $browser->visit($alamat)->waitForText('Masuk.')->assertPathIs('/login');
            }
        });
    }

    /** TC-12 Eksepsi: mahasiswa yang membuka halaman admin ditolak (403). */
    public function test_tc12_mahasiswa_membuka_halaman_admin_ditolak(): void
    {
        $user = $this->akun();

        $this->browse(function (Browser $browser) use ($user) {
            $this->masuk($browser, $user->email)->waitForLocation('/dashboard');

            $browser->visit('/admin')->assertSee('403');
        });
    }
}
