<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Challenge;
use App\Models\Claim;
use App\Models\User;
use App\Services\ClaimPhotoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClaimTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake(ClaimPhotoService::DISK);
    }

    private function mahasiswa(array $attributes = []): User
    {
        static $counter = 0;
        $counter++;

        return User::factory()->create(array_merge([
            'name' => 'Mahasiswa Satu',
            'username' => 'klaim'.$counter,
            'email' => "klaim{$counter}@student.telkomuniversity.ac.id",
            'role' => User::ROLE_MAHASISWA,
        ], $attributes));
    }

    private function challenge(array $override = []): Challenge
    {
        $category = Category::firstOrCreate(['name' => 'Energi']);

        return Challenge::create(array_merge([
            'category_id' => $category->id,
            'created_by' => User::factory()->create()->id,
            'title' => 'Bawa tumbler',
            'description' => 'Deskripsi',
            'difficulty' => 'Mudah',
            'points' => 20,
            'is_active' => true,
            'proof_criteria' => 'Foto tumbler',
            'estimated_co2e_gram' => 83,
            'emission_source' => 'Contoh',
        ], $override));
    }

    /** PBI-15: tamu tidak bisa membuka halaman klaim. */
    public function test_tamu_diarahkan_ke_login(): void
    {
        $challenge = $this->challenge();

        $this->get(route('claims.index'))->assertRedirect(route('login'));
        $this->get(route('claims.create', $challenge))->assertRedirect(route('login'));
    }

    /** PBI-15 & PBI-17: unggah foto tersimpan privat dengan hash utuh. */
    public function test_mahasiswa_bisa_mengunggah_bukti_foto_tersimpan_privat(): void
    {
        $user = $this->mahasiswa();
        $challenge = $this->challenge();
        $upload = UploadedFile::fake()->image('bukti.jpg', 640, 480);

        $this->actingAs($user)
            ->post(route('claims.store', $challenge), ['photo' => $upload])
            ->assertRedirect();

        $claim = Claim::query()->sole();
        $disk = Storage::disk(ClaimPhotoService::DISK);

        $this->assertSame($user->id, $claim->user_id);
        $this->assertSame($challenge->id, $claim->challenge_id);
        $this->assertSame(Claim::STATUS_PENDING, $claim->status);
        $this->assertTrue($claim->needs_review);

        // Foto tersimpan byte asli di penyimpanan privat (disk lokal).
        $this->assertStringStartsWith(ClaimPhotoService::DIRECTORY.'/'.$user->id.'/', $claim->proof_path);
        $disk->assertExists($claim->proof_path);
        $this->assertSame(hash('sha256', $disk->get($claim->proof_path)), $claim->photo_hash);

        // Fake image tidak punya EXIF, sehingga waktu foto null (EXIF diuji via file nyata).
        $this->assertNull($claim->photo_taken_at);
    }

    /** PBI-15: file yang bukan gambar ditolak. */
    public function test_file_bukan_gambar_ditolak(): void
    {
        $user = $this->mahasiswa();
        $challenge = $this->challenge();

        $this->actingAs($user)
            ->post(route('claims.store', $challenge), [
                'photo' => UploadedFile::fake()->create('dokumen.pdf', 100),
            ])
            ->assertSessionHasErrors('photo');

        $this->assertSame(0, Claim::count());
    }

    /** PBI-16: satu klaim per challenge per hari. */
    public function test_satu_klaim_per_challenge_per_hari(): void
    {
        $user = $this->mahasiswa();
        $challenge = $this->challenge();

        $this->actingAs($user)
            ->post(route('claims.store', $challenge), [
                'photo' => UploadedFile::fake()->image('bukti.jpg', 320, 240),
            ])
            ->assertRedirect();

        $this->actingAs($user)
            ->from(route('claims.create', $challenge))
            ->post(route('claims.store', $challenge), [
                'photo' => UploadedFile::fake()->image('bukti-lain.jpg', 320, 240),
            ])
            ->assertSessionHasErrors('photo');

        $this->assertSame(1, Claim::count());
    }

    /** PBI-16: foto ber-hash sama ditolak walau challenge berbeda. */
    public function test_foto_dengan_hash_sama_ditolak(): void
    {
        $user = $this->mahasiswa();
        $challengeA = $this->challenge(['title' => 'Aksi A']);
        $challengeB = $this->challenge(['title' => 'Aksi B']);

        // Byte identik (gambar solid), sehingga hash sama.
        $upload = UploadedFile::fake()->image('bukti.jpg', 100, 100);

        $this->actingAs($user)
            ->post(route('claims.store', $challengeA), ['photo' => $upload])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('claims.store', $challengeB), ['photo' => $upload])
            ->assertSessionHasErrors('photo');

        $this->assertSame(1, Claim::count());
    }

    /** PBI-17: foto privat hanya bisa diakses pemilik atau admin. */
    public function test_foto_hanya_dapat_dilihat_pemilik_atau_admin(): void
    {
        $user = $this->mahasiswa();
        $challenge = $this->challenge();
        $upload = UploadedFile::fake()->image('bukti.jpg', 320, 240);

        $this->actingAs($user)->post(route('claims.store', $challenge), ['photo' => $upload]);
        $claim = Claim::query()->sole();

        $this->actingAs($user)
            ->get(route('claims.photo', $claim))
            ->assertOk();

        $this->actingAs($this->mahasiswa())
            ->get(route('claims.photo', $claim))
            ->assertNotFound();

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('claims.photo', $claim))
            ->assertOk();
    }

    /** PBI-18: alasan penolakan tampil, dan kirim ulang mereset status & mengganti foto. */
    public function test_alasan_penolakan_tampil_dan_kirim_ulang_mereset_status(): void
    {
        $user = $this->mahasiswa();
        $challenge = $this->challenge();
        $disk = Storage::disk(ClaimPhotoService::DISK);

        $disk->put(ClaimPhotoService::DIRECTORY.'/'.$user->id.'/lama.jpg', 'foto-lama');

        $claim = Claim::create([
            'user_id' => $user->id,
            'challenge_id' => $challenge->id,
            'proof_path' => ClaimPhotoService::DIRECTORY.'/'.$user->id.'/lama.jpg',
            'photo_hash' => hash('sha256', 'foto-lama'),
            'claim_date' => today()->toDateString(),
            'status' => Claim::STATUS_REJECTED,
            'cancel_reason' => 'Foto terlalu gelap dan tidak terbaca.',
        ]);

        $this->actingAs($user)
            ->get(route('claims.show', $claim))
            ->assertOk()
            ->assertSee('Foto terlalu gelap dan tidak terbaca.')
            ->assertSee(route('claims.resubmit', $claim), false);

        // Kirim ulang hanya untuk klaim ditolak; foto baru menggantikan yang lama.
        $this->actingAs($user)
            ->post(route('claims.resubmit', $claim), [
                'photo' => UploadedFile::fake()->image('baru.jpg', 320, 240),
                'note' => 'Foto ulang di tempat terang.',
            ])
            ->assertRedirect(route('claims.show', $claim));

        $claim->refresh();

        $this->assertSame(Claim::STATUS_PENDING, $claim->status);
        $this->assertNull($claim->cancel_reason);
        $this->assertNull($claim->reviewed_at);
        $this->assertNull($claim->reviewed_by);
        $this->assertSame('Foto ulang di tempat terang.', $claim->note);
        $this->assertStringNotContainsString('lama.jpg', $claim->proof_path);
        $disk->assertMissing(ClaimPhotoService::DIRECTORY.'/'.$user->id.'/lama.jpg');
        $disk->assertExists($claim->proof_path);
    }

    /** PBI-18: klaim yang belum ditolak tidak bisa dikirim ulang. */
    public function test_klaim_pending_tidak_bisa_dikirim_ulang(): void
    {
        $user = $this->mahasiswa();
        $challenge = $this->challenge();

        $this->actingAs($user)->post(route('claims.store', $challenge), [
            'photo' => UploadedFile::fake()->image('bukti.jpg', 320, 240),
        ]);

        $claim = Claim::query()->sole();

        $this->actingAs($user)
            ->post(route('claims.resubmit', $claim), [
                'photo' => UploadedFile::fake()->image('baru.jpg', 320, 240),
            ])
            ->assertSessionHasErrors('photo');

        $this->assertSame(Claim::STATUS_PENDING, $claim->fresh()->status);
    }
}
