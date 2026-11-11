<?php

namespace App\Services;

use App\Exceptions\InvalidClaimPhotoException;
use App\Models\Claim;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClaimPhotoService
{
    public const DISK = 'local';

    public const DIRECTORY = 'claim-proofs';

    /**
     * Simpan foto bukti apa adanya (byte asli) agar hash SHA-256 dan EXIF tetap utuh.
     *
     * @return array{path: string, hash: string, taken_at: ?string}
     */
    public function store(int $userId, UploadedFile $file): array
    {
        $bytes = $file->get();
        $hash = hash('sha256', $bytes);

        if (Claim::where('photo_hash', $hash)->exists()) {
            throw new InvalidClaimPhotoException('Foto ini sudah pernah dipakai. Gunakan foto lain.');
        }

        $path = self::DIRECTORY.'/'.$userId.'/'.Str::uuid().'.'.$this->extension($file);

        if (! Storage::disk(self::DISK)->put($path, $bytes)) {
            throw new InvalidClaimPhotoException('Foto bukti gagal disimpan. Coba lagi.');
        }

        return [
            'path' => $path,
            'hash' => $hash,
            'taken_at' => $this->takenAt($file->getRealPath()),
        ];
    }

    public function delete(?string $path): void
    {
        if ($path) {
            Storage::disk(self::DISK)->delete($path);
        }
    }

    private function extension(UploadedFile $file): string
    {
        return $file->getMimeType() === 'image/png' ? 'png' : 'jpg';
    }

    /** Waktu foto diambil dari EXIF DateTimeOriginal (format "YYYY:MM:DD HH:MM:SS"). */
    private function takenAt(?string $realPath): ?string
    {
        if (! $realPath || ! function_exists('exif_read_data')) {
            return null;
        }

        $exif = @exif_read_data($realPath);
        $raw = $exif['DateTimeOriginal'] ?? $exif['DateTimeDigitized'] ?? null;

        if (! is_string($raw) || ! preg_match('/^(\d{4}):(\d{2}):(\d{2}) (\d{2}):(\d{2}):(\d{2})$/', $raw, $m)) {
            return null;
        }

        return sprintf('%s-%s-%s %s:%s:%s', $m[1], $m[2], $m[3], $m[4], $m[5], $m[6]);
    }
}
