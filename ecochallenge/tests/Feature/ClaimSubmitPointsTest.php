<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Challenge;
use App\Models\Claim;
use App\Models\User;
use App\Services\ClaimPhotoService;
use App\Services\LeaderboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

/**
 * PBI-19 (SCRUM-31) lewat alur sungguhan UC-06: mahasiswa mengirim bukti, klaim yang lolos
 * verifikasi langsung diterima, dan poin bertambah di basis data dalam satu transaksi.
 */
class ClaimSubmitPointsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Cache::flush();
        Storage::fake(ClaimPhotoService::DISK);
    }

    private function mahasiswa(int $poin, string $username): User
    {
        return User::factory()->create([
            'username' => $username,
            'email' => $username.'@example.com',
            'role' => User::ROLE_MAHASISWA,
            'is_active' => true,
            'total_points' => $poin,
        ]);
    }

    private function challenge(int $poin = 10, int $co2 = 250): Challenge
    {
        $category = Category::create(['name' => 'Uji '.uniqid(), 'description' => 'Kategori uji']);

        return Challenge::create([
            'category_id' => $category->id,
            'title' => 'Pisahkan Sampah',
            'description' => 'Deskripsi',
            'difficulty' => 'mudah',
            'points' => $poin,
            'is_active' => true,
            'proof_criteria' => 'Foto bukti',
            'estimated_co2e_gram' => $co2,
        ]);
    }

    private function kirimBukti(User $user, Challenge $challenge)
    {
        return $this->actingAs($user)->post(route('claims.store', $challenge), [
            'photo' => UploadedFile::fake()->image('bukti.jpg', 320, 240),
        ]);
    }

    /** TC-19-01: klaim challenge 10 poin yang diterima menaikkan total poin dari 20 menjadi 30. */
    public function test_klaim_diterima_menaikkan_total_poin_20_menjadi_30(): void
    {
        $saya = $this->mahasiswa(20, 'rani.hijau');
        $challenge = $this->challenge(10, 250);

        $this->kirimBukti($saya, $challenge)
            ->assertRedirect()
            ->assertSessionHas('status', 'Klaim diterima. +10 poin ditambahkan.');

        $claim = Claim::query()->sole();
        $this->assertSame(Claim::STATUS_APPROVED, $claim->status);
        $this->assertSame(10, $claim->points_awarded);
        $this->assertSame(250, $claim->co2e_awarded_gram);
        $this->assertSame(30, $saya->fresh()->total_points);
    }

    /** UC-06 langkah 9: halaman Hasil Klaim menampilkan poin, total poin, peringkat, dan emisi. */
    public function test_halaman_hasil_klaim_menampilkan_poin_peringkat_dan_emisi(): void
    {
        $saya = $this->mahasiswa(20, 'rani.hijau');
        $this->mahasiswa(25, 'lawan.satu');

        $this->kirimBukti($saya, $this->challenge(10, 250));
        $claim = Claim::query()->sole();

        $this->actingAs($saya->fresh())
            ->get(route('claims.show', $claim))
            ->assertOk()
            ->assertSee('Diterima')
            ->assertSee('+10')
            ->assertSee('30')
            ->assertSee('#1')
            ->assertSee('250');
    }

    /** TC-19-03 / UC-06 alur 7a: transaksi gagal, poin tetap, klaim tidak tersimpan, foto dihapus. */
    public function test_bila_transaksi_gagal_poin_tidak_berubah_dan_foto_dihapus(): void
    {
        $saya = $this->mahasiswa(20, 'rani.hijau');
        $challenge = $this->challenge(10);

        Claim::creating(function () {
            throw new RuntimeException('penyimpanan klaim gagal');
        });

        $this->kirimBukti($saya, $challenge)
            ->assertSessionHasErrors(['photo' => 'Klaim gagal disimpan. Silakan coba lagi.']);

        $this->assertSame(0, Claim::count());
        $this->assertSame(20, $saya->fresh()->total_points);
        $this->assertSame([], Storage::disk(ClaimPhotoService::DISK)->allFiles());
    }

    /** TC-20-06: leaderboard langsung memakai poin baru setelah klaim diterima. */
    public function test_leaderboard_langsung_berubah_setelah_klaim_diterima(): void
    {
        $saya = $this->mahasiswa(20, 'rani.hijau');
        $lawan = $this->mahasiswa(25, 'lawan.satu');
        $leaderboard = app(LeaderboardService::class);

        $this->assertSame($lawan->id, $leaderboard->top()[0]['id']);

        $this->kirimBukti($saya, $this->challenge(10));

        $this->assertSame($saya->id, $leaderboard->top()[0]['id']);
        $this->assertSame(30, $leaderboard->top()[0]['total_points']);
    }

    /** Verifikasi gagal (bukan gambar): klaim tidak disimpan dan poin tidak bertambah. */
    public function test_verifikasi_gagal_tidak_menambah_poin(): void
    {
        $saya = $this->mahasiswa(20, 'rani.hijau');

        $this->actingAs($saya)->post(route('claims.store', $this->challenge(10)), [
            'photo' => UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('photo');

        $this->assertSame(0, Claim::count());
        $this->assertSame(20, $saya->fresh()->total_points);
    }
}
