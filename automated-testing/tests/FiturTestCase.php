<?php

namespace Tests\Browser\Fitur;

use App\Models\Category;
use App\Models\Challenge;
use App\Models\User;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Dasar semua tes browser per fitur.
 *
 * Tes ini dibuat untuk DITONTON: browser tampil (tidak headless), mengetik, mengklik, dan
 * berpindah halaman sendiri. Jeda antarlangkah diatur lewat DUSK_JEDA (milidetik, bawaan 700).
 */
abstract class FiturTestCase extends DuskTestCase
{
    use DatabaseMigrations;

    protected const KATA_SANDI = 'Rahasia123!';

    /**
     * Sama seperti DuskTestCase::driver(), tetapi boleh memakai browser Chromium lain
     * (mis. Brave) lewat DUSK_CHROME_BINARY. Tanpa itu, Dusk memakai Chrome bawaan sistem.
     * Browser selalu memakai profil sementara yang bersih, bukan profil harian pengguna.
     */
    protected function driver(): RemoteWebDriver
    {
        $opsi = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1366,900',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
            '--no-first-run',
            '--no-default-browser-check',
        ])->unless($this->hasHeadlessDisabled(), fn ($a) => $a->merge(['--disable-gpu', '--headless=new']))->all());

        if ($binary = env('DUSK_CHROME_BINARY')) {
            $opsi->setBinary($binary);
        }

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(ChromeOptions::CAPABILITY, $opsi)
        );
    }

    /**
     * Dusk memakai ulang jendela browser antartes. Karena basis data dibuat ulang tiap tes
     * (id pengguna mulai dari 1 lagi), sesi login tes sebelumnya bisa membuat browser tampak
     * sudah login sebagai orang lain. Maka setiap tes dimulai dengan keluar dan menghapus cookie.
     */
    public function browse(\Closure $callback)
    {
        return parent::browse(function (...$browsers) use ($callback) {
            foreach ($browsers as $browser) {
                try {
                    $browser->logout();
                    $browser->driver->manage()->deleteAllCookies();
                } catch (\Throwable) {
                    // Halaman belum dimuat; tidak ada sesi yang perlu dibersihkan.
                }
            }

            return $callback(...$browsers);
        });
    }

    /** Jeda singkat agar tiap langkah terlihat oleh penonton. */
    protected function jeda(Browser $browser, ?int $ms = null): Browser
    {
        return $browser->pause($ms ?? (int) env('DUSK_JEDA', 700));
    }

    protected function mahasiswa(array $atribut = []): User
    {
        static $urut = 0;
        $urut++;

        return User::factory()->create(array_merge([
            'name' => 'Rani Hijau '.$urut,
            'username' => 'rani.hijau'.$urut,
            'email' => "rani{$urut}@student.telkomuniversity.ac.id",
            'password' => bcrypt(self::KATA_SANDI),
            'role' => User::ROLE_MAHASISWA,
            'is_active' => true,
            'total_points' => 0,
        ], $atribut));
    }

    protected function admin(array $atribut = []): User
    {
        return User::factory()->create(array_merge([
            'name' => 'Admin EcoChallenge',
            'username' => 'admin.uji',
            'email' => 'admin.uji@ecochallenge.test',
            'password' => bcrypt(self::KATA_SANDI),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
            'total_points' => 0,
        ], $atribut));
    }

    protected function kategori(string $nama = 'Transportasi'): Category
    {
        return Category::create(['name' => $nama, 'description' => 'Kategori '.$nama, 'is_active' => true]);
    }

    protected function challenge(?Category $kategori = null, array $atribut = []): Challenge
    {
        $kategori ??= $this->kategori('Kategori '.uniqid());

        return Challenge::create(array_merge([
            'category_id' => $kategori->id,
            'title' => 'Berjalan Kaki ke Kampus',
            'description' => 'Berjalan kaki untuk perjalanan jarak dekat ke kampus.',
            'difficulty' => 'mudah',
            'points' => 20,
            'is_active' => true,
            'proof_criteria' => 'Foto rute atau jam berjalan kaki.',
            'estimated_co2e_gram' => 500,
            'emission_source' => 'Estimasi emisi kendaraan bermotor yang dihindari',
        ], $atribut));
    }

    /** Masuk lewat formulir login sungguhan (diketik dan diklik oleh browser). */
    protected function masuk(Browser $browser, User $user, string $kataSandi = self::KATA_SANDI): Browser
    {
        $browser->visit('/login')->waitForText('Masuk.');
        $this->jeda($browser, 300);
        $browser->type('email', $user->email);
        $this->jeda($browser, 300);
        $browser->type('password', $kataSandi);
        $this->jeda($browser, 300);
        $browser->press('Masuk');

        return $browser;
    }

    /** Buka menu akun di navbar lalu klik Keluar. */
    protected function keluar(Browser $browser): Browser
    {
        $browser->click('header button[aria-haspopup="menu"]')->pause(500);

        return $browser->press('Keluar');
    }

    /**
     * Membuat berkas PNG kecil berwarna unik (tanpa ekstensi GD) sebagai foto bukti.
     * Warna berbeda menghasilkan hash SHA-256 berbeda.
     */
    protected function fotoPng(int $warna): string
    {
        $dir = storage_path('app/dusk-fixtures');
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $lebar = $tinggi = 24;
        $r = ($warna * 47) % 256;
        $g = ($warna * 91 + 40) % 256;
        $b = ($warna * 133 + 80) % 256;
        $baris = "\x00".str_repeat(chr($r).chr($g).chr($b), $lebar);
        $mentah = str_repeat($baris, $tinggi);

        $chunk = fn (string $tipe, string $data) => pack('N', strlen($data)).$tipe.$data.pack('N', crc32($tipe.$data));
        $png = "\x89PNG\r\n\x1a\n"
            .$chunk('IHDR', pack('NNCCCCC', $lebar, $tinggi, 8, 2, 0, 0, 0))
            .$chunk('IDAT', gzcompress($mentah))
            .$chunk('IEND', '');

        $path = $dir.DIRECTORY_SEPARATOR."bukti-{$warna}.png";
        file_put_contents($path, $png);

        return $path;
    }

    /**
     * JPEG kecil berwarna unik (butuh ekstensi GD). Bila $tanggal diisi ("Y-m-d H:i:s"), EXIF
     * DateTimeOriginal disuntikkan ke berkas sehingga layanan foto bisa membaca waktu pengambilan.
     */
    protected function fotoJpeg(int $warna, ?string $tanggal = null): string
    {
        $dir = storage_path('app/dusk-fixtures');
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $gambar = imagecreatetruecolor(32, 32);
        imagefill($gambar, 0, 0, imagecolorallocate($gambar, ($warna * 47) % 256, ($warna * 91 + 40) % 256, ($warna * 133 + 80) % 256));
        ob_start();
        imagejpeg($gambar, null, 90);
        $jpeg = ob_get_clean();

        if ($tanggal !== null) {
            $waktu = date('Y:m:d H:i:s', strtotime($tanggal))."\0"; // 20 byte
            $tiff = 'II'.pack('v', 42).pack('V', 8)                          // header TIFF
                .pack('v', 1).pack('vvVV', 0x8769, 4, 1, 26).pack('V', 0)    // IFD0 -> penunjuk ExifIFD
                .pack('v', 1).pack('vvVV', 0x9003, 2, 20, 44).pack('V', 0)   // ExifIFD -> DateTimeOriginal
                .$waktu;
            $app1 = "Exif\0\0".$tiff;
            $jpeg = substr($jpeg, 0, 2)."\xFF\xE1".pack('n', strlen($app1) + 2).$app1.substr($jpeg, 2);
        }

        $path = $dir.DIRECTORY_SEPARATOR."bukti-{$warna}".($tanggal ? '-exif' : '').'.jpg';
        file_put_contents($path, $jpeg);

        return $path;
    }

    /** PNG berukuran sekitar 3 MB (data acak tidak bisa dikompres) untuk menguji batas 2 MB. */
    protected function fotoPngBesar(): string
    {
        $dir = storage_path('app/dusk-fixtures');
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $lebar = $tinggi = 1000;
        $mentah = '';
        for ($i = 0; $i < $tinggi; $i++) {
            $mentah .= "\x00".random_bytes($lebar * 3);
        }

        $chunk = fn (string $tipe, string $data) => pack('N', strlen($data)).$tipe.$data.pack('N', crc32($tipe.$data));
        $png = "\x89PNG\r\n\x1a\n"
            .$chunk('IHDR', pack('NNCCCCC', $lebar, $tinggi, 8, 2, 0, 0, 0))
            .$chunk('IDAT', gzcompress($mentah, 0))
            .$chunk('IEND', '');

        $path = $dir.DIRECTORY_SEPARATOR.'bukti-besar.png';
        file_put_contents($path, $png);

        return $path;
    }

    protected function berkasPdf(): string
    {
        $dir = storage_path('app/dusk-fixtures');
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $path = $dir.DIRECTORY_SEPARATOR.'dokumen.pdf';
        file_put_contents($path, "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF\n");

        return $path;
    }

    protected function berkasTeks(): string
    {
        $dir = storage_path('app/dusk-fixtures');
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $path = $dir.DIRECTORY_SEPARATOR.'bukan-gambar.txt';
        file_put_contents($path, 'ini bukan foto');

        return $path;
    }
}
