<?php

namespace App\Services;

use App\Exceptions\InvalidProfilePhotoException;
use App\Models\User;
use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfilePhotoService
{
    public const DISK = 'local';

    public const DIRECTORY = 'profile-photos';

    public const MAX_DIMENSION = 512;

    public const JPEG_QUALITY = 80;

    public function replace(User $user, UploadedFile $file): string
    {
        $jpeg = $this->compress($file);

        $disk = Storage::disk(self::DISK);
        $path = self::DIRECTORY.'/'.$user->getKey().'/'.Str::uuid().'.jpg';

        if (! $disk->put($path, $jpeg)) {
            throw new InvalidProfilePhotoException('Foto profil gagal disimpan. Coba lagi.');
        }

        $oldPath = $user->profile_photo_path;

        $user->forceFill(['profile_photo_path' => $path])->save();

        if ($oldPath && $oldPath !== $path) {
            $disk->delete($oldPath);
        }

        return $path;
    }

    public function compress(UploadedFile $file): string
    {
        $sourcePath = $file->getRealPath();
        $info = $sourcePath ? @getimagesize($sourcePath) : false;

        $source = match ($info[2] ?? null) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG => @imagecreatefrompng($sourcePath),
            default => false,
        };

        if (! $source instanceof GdImage) {
            throw new InvalidProfilePhotoException('Foto tidak bisa dibaca. Gunakan berkas JPG atau PNG yang valid.');
        }

        if ($info[2] === IMAGETYPE_JPEG) {
            $source = $this->applyOrientation($source, $sourcePath);
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, self::MAX_DIMENSION / max($width, $height));
        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));

        $canvas = imagecreatetruecolor($newWidth, $newHeight);

        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        ob_start();
        imagejpeg($canvas, null, self::JPEG_QUALITY);
        $jpeg = (string) ob_get_clean();

        imagedestroy($source);
        imagedestroy($canvas);

        if ($jpeg === '') {
            throw new InvalidProfilePhotoException('Foto gagal dikompres. Coba berkas lain.');
        }

        return $jpeg;
    }

    private function applyOrientation(GdImage $image, string $path): GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path, 'IFD0');
        $angle = match ((int) ($exif['Orientation'] ?? 1)) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);

        if (! $rotated instanceof GdImage) {
            return $image;
        }

        imagedestroy($image);

        return $rotated;
    }
}
