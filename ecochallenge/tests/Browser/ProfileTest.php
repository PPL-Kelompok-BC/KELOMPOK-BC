<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ProfileTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function mahasiswa(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'name' => 'Rani Hijau',
            'username' => 'rani.hijau',
            'email' => 'rani@student.telkomuniversity.ac.id',
            'role' => 'mahasiswa',
        ], $attributes));
    }

    public function test_halaman_profil_menampilkan_data_diri(): void
    {
        $user = $this->mahasiswa();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visitRoute('profile.show')
                ->assertSeeIn('@profile-name', 'Rani Hijau')
                ->assertSeeIn('@profile-username', '@rani.hijau')
                ->assertSeeIn('@profile-email', 'rani@student.telkomuniversity.ac.id');
        });
    }

    public function test_mahasiswa_mengubah_profil_lalu_username_bentrok_ditolak(): void
    {
        $user = $this->mahasiswa();
        $this->mahasiswa(['username' => 'sudah.dipakai', 'email' => 'lain@student.telkomuniversity.ac.id']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visitRoute('profile.show')
                ->click('@edit-profile')
                ->assertRouteIs('profile.edit')
                ->clear('@name')->type('@name', 'Rani Lestari')
                ->clear('@username')->type('@username', 'rani.lestari')
                ->press('@save-profile')
                ->assertRouteIs('profile.show')
                ->assertSeeIn('@profile-status', 'Profil berhasil diperbarui.')
                ->assertSeeIn('@profile-name', 'Rani Lestari')
                ->assertSeeIn('@profile-username', '@rani.lestari');

            $browser->visitRoute('profile.edit')
                ->clear('@username')->type('@username', 'sudah.dipakai')
                ->press('@save-profile')
                ->assertRouteIs('profile.edit')
                ->assertSee('Username sudah dipakai. Pilih username lain.');
        });
    }

    public function test_unggah_foto_profil_dan_tolak_berkas_bukan_gambar(): void
    {
        $user = $this->mahasiswa();
        $photo = UploadedFile::fake()->image('foto.jpg', 1200, 900);
        $document = UploadedFile::fake()->create('dokumen.pdf', 50, 'application/pdf');

        $this->browse(function (Browser $browser) use ($user, $photo, $document) {
            $browser->loginAs($user)
                ->visitRoute('profile.edit')
                ->attach('@photo', $photo->getRealPath())
                ->press('@save-photo')
                ->assertRouteIs('profile.show')
                ->assertSeeIn('@profile-status', 'Foto profil berhasil diperbarui.')
                ->assertPresent('@profile-photo');

            $browser->visitRoute('profile.edit')
                ->attach('@photo', $document->getRealPath())
                ->press('@save-photo')
                ->assertRouteIs('profile.edit')
                ->assertSee('Foto harus berformat JPG atau PNG.')
                ->assertPresent('@profile-photo');
        });
    }
}
