<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\ProfilePhotoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake(ProfilePhotoService::DISK);

        if (! Route::has('login')) {
            Route::get('/login', fn () => 'login')->name('login');
            Route::getRoutes()->refreshNameLookups();
        }
    }

    private function mahasiswa(array $attributes = []): User
    {
        static $counter = 0;
        $counter++;

        return User::factory()->create(array_merge([
            'name' => 'Mahasiswa Satu',
            'username' => 'mahasiswa'.$counter,
            'email' => "mahasiswa{$counter}@student.telkomuniversity.ac.id",
            'role' => 'mahasiswa',
        ], $attributes));
    }

    public function test_tamu_yang_membuka_profil_diarahkan_ke_login(): void
    {
        $this->get(route('profile.show'))->assertRedirect(route('login'));
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
    }

    public function test_profil_menampilkan_nama_username_email_dan_foto(): void
    {
        $user = $this->mahasiswa(['name' => 'Rani Hijau', 'username' => 'rani.hijau']);

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Rani Hijau')
            ->assertSee('rani.hijau')
            ->assertSee($user->email)
            ->assertSee('RH');

        $user->forceFill(['profile_photo_path' => 'profile-photos/1/foto.jpg'])->save();

        $this->actingAs($user)
            ->get(route('profile.show'))
            ->assertSee(route('profile.photo', $user), false);
    }

    public function test_mahasiswa_bisa_mengubah_nama_username_dan_email(): void
    {
        $user = $this->mahasiswa();

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => '  Budi Lestari  ',
                'username' => 'Budi.Lestari',
                'email' => 'BUDI@student.telkomuniversity.ac.id',
            ])
            ->assertRedirect(route('profile.show'))
            ->assertSessionHas('status', 'Profil berhasil diperbarui.');

        $user->refresh();

        $this->assertSame('Budi Lestari', $user->name);
        $this->assertSame('budi.lestari', $user->username);
        $this->assertSame('budi@student.telkomuniversity.ac.id', $user->email);
    }

    public function test_menyimpan_username_dan_email_sendiri_tidak_ditolak(): void
    {
        $user = $this->mahasiswa();

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Nama Baru',
                'username' => $user->username,
                'email' => $user->email,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('Nama Baru', $user->fresh()->name);
    }

    public function test_username_milik_orang_lain_ditolak_dengan_pesan(): void
    {
        $this->mahasiswa(['username' => 'sudah.dipakai']);
        $user = $this->mahasiswa();
        $oldUsername = $user->username;

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'username' => 'sudah.dipakai',
                'email' => $user->email,
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors(['username' => 'Username sudah dipakai. Pilih username lain.']);

        $this->assertSame($oldUsername, $user->fresh()->username);
    }

    public function test_email_milik_orang_lain_dan_format_salah_ditolak(): void
    {
        $other = $this->mahasiswa();
        $user = $this->mahasiswa();

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $other->email,
            ])
            ->assertSessionHasErrors(['email' => 'Email sudah dipakai akun lain.']);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => '',
                'username' => 'ab',
                'email' => 'bukan-email',
            ])
            ->assertSessionHasErrors(['name', 'username', 'email']);
    }

    public function test_username_dengan_karakter_terlarang_ditolak(): void
    {
        $user = $this->mahasiswa();

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'username' => 'nama pakai spasi!',
                'email' => $user->email,
            ])
            ->assertSessionHasErrors('username');
    }

    public function test_foto_jpg_tersimpan_dalam_ukuran_terkompres(): void
    {
        $user = $this->mahasiswa();
        $upload = UploadedFile::fake()->image('foto.jpg', 2400, 1800);

        $this->actingAs($user)
            ->post(route('profile.photo.update'), ['photo' => $upload])
            ->assertRedirect(route('profile.show'))
            ->assertSessionHas('status', 'Foto profil berhasil diperbarui.');

        $path = $user->fresh()->profile_photo_path;
        $disk = Storage::disk(ProfilePhotoService::DISK);

        $this->assertNotNull($path);
        $this->assertStringStartsWith('profile-photos/'.$user->id.'/', $path);
        $disk->assertExists($path);

        [$width, $height, $type] = getimagesizefromstring($disk->get($path));
        $this->assertSame(IMAGETYPE_JPEG, $type);
        $this->assertSame(512, $width);
        $this->assertSame(384, $height);
    }

    public function test_foto_png_diterima_dan_disimpan_sebagai_jpeg(): void
    {
        $user = $this->mahasiswa();

        $this->actingAs($user)
            ->post(route('profile.photo.update'), [
                'photo' => UploadedFile::fake()->image('foto.png', 300, 300),
            ])
            ->assertSessionHasNoErrors();

        $disk = Storage::disk(ProfilePhotoService::DISK);
        [$width, , $type] = getimagesizefromstring($disk->get($user->fresh()->profile_photo_path));

        $this->assertSame(IMAGETYPE_JPEG, $type);
        $this->assertSame(300, $width);
    }

    public function test_mengganti_foto_menghapus_foto_lama(): void
    {
        $user = $this->mahasiswa();
        $disk = Storage::disk(ProfilePhotoService::DISK);

        $this->actingAs($user)->post(route('profile.photo.update'), [
            'photo' => UploadedFile::fake()->image('lama.jpg', 400, 400),
        ]);
        $oldPath = $user->fresh()->profile_photo_path;

        $this->actingAs($user)->post(route('profile.photo.update'), [
            'photo' => UploadedFile::fake()->image('baru.jpg', 400, 400),
        ]);
        $newPath = $user->fresh()->profile_photo_path;

        $this->assertNotSame($oldPath, $newPath);
        $disk->assertMissing($oldPath);
        $disk->assertExists($newPath);
    }

    public function test_berkas_selain_jpg_png_ditolak_dan_foto_lama_tetap(): void
    {
        $user = $this->mahasiswa();

        $this->actingAs($user)->post(route('profile.photo.update'), [
            'photo' => UploadedFile::fake()->image('lama.jpg', 400, 400),
        ]);
        $oldPath = $user->fresh()->profile_photo_path;

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->post(route('profile.photo.update'), [
                'photo' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('photo');

        $this->actingAs($user)->post(route('profile.photo.update'), [
            'photo' => UploadedFile::fake()->image('animasi.gif', 200, 200),
        ])->assertSessionHasErrors('photo');

        $this->assertSame($oldPath, $user->fresh()->profile_photo_path);
        Storage::disk(ProfilePhotoService::DISK)->assertExists($oldPath);
    }

    public function test_foto_lebih_dari_2_mb_ditolak(): void
    {
        $user = $this->mahasiswa();

        $this->actingAs($user)
            ->post(route('profile.photo.update'), [
                'photo' => UploadedFile::fake()->image('besar.jpg', 800, 800)->size(2049),
            ])
            ->assertSessionHasErrors(['photo' => 'Ukuran foto maksimal 2 MB.']);

        $this->assertNull($user->fresh()->profile_photo_path);
    }

    public function test_foto_profil_bisa_dibuka_pengguna_login_tetapi_tidak_oleh_tamu(): void
    {
        $user = $this->mahasiswa();

        $this->actingAs($user)->post(route('profile.photo.update'), [
            'photo' => UploadedFile::fake()->image('foto.jpg', 300, 300),
        ]);

        $this->actingAs($user)
            ->get(route('profile.photo', $user))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg');

        $this->actingAs($this->mahasiswa())
            ->get(route('profile.photo', $this->mahasiswa()))
            ->assertNotFound();

        auth()->logout();

        $this->get(route('profile.photo', $user))->assertRedirect(route('login'));
    }
}
