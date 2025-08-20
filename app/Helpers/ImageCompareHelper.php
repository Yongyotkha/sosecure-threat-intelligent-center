<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;


class ImageCompareHelper
{
    public static function compareLargeImagesBySlice($img1Path, $img2Path, $sliceHeight = 2000)
{
    $imageSize1 = getimagesize($img1Path);
    $imageSize2 = getimagesize($img2Path);

    $width1 = $imageSize1[0];
    $height1 = $imageSize1[1];
    $width2 = $imageSize2[0];
    $height2 = $imageSize2[1];

    $maxHeight = min($height1, $height2);
    $sliceCount = ceil($maxHeight / $sliceHeight);

    $image1Hash = null;
    $image2Hash = null;
    $diffSlices = 0;
    $sliceDiffs = [];

    for ($i = 0; $i < $sliceCount; $i++) {
        $y = $i * $sliceHeight;
        $actualSliceHeight = min($sliceHeight, $maxHeight - $y);

        // Crop เฉพาะ slice ที่ต้องการโหลดจาก disk
        $slice1 = self::cropImage($img1Path, 0, $y, $width1, $actualSliceHeight);
        $slice2 = self::cropImage($img2Path, 0, $y, $width2, $actualSliceHeight);

        if (!$slice1 || !$slice2) {
            \Log::warning("[compareLargeImagesBySlice] โหลด slice ไม่ได้ที่ index $i");
            continue;
        }

        $tmpPath1 = sys_get_temp_dir() . "/slice1_$i.jpg";
        $tmpPath2 = sys_get_temp_dir() . "/slice2_$i.jpg";

        imagejpeg($slice1, $tmpPath1);
        imagejpeg($slice2, $tmpPath2);

        $cmp = new compareImages($tmpPath1);
        $hash1 = $cmp->getHasString();
        $hash2 = $cmp->hasStringImage($tmpPath2);
        $diff = $cmp->compareHash($hash2);

        if ($i === 0) {
            $image1Hash = $hash1;
            $image2Hash = $hash2;
        }

        \Log::info("[Slice $i] diff = $diff");

        $sliceDiffs[] = [
            'index' => $i,
            'diff' => $diff,
            'hash1' => $hash1,
            'hash2' => $hash2,
        ];

        if ($diff > 10) {
            $diffSlices++;
        }

        unlink($tmpPath1);
        unlink($tmpPath2);
        imagedestroy($slice1);
        imagedestroy($slice2);
    }

    $percentDiff = round(($diffSlices / $sliceCount) * 100, 2);
    \Log::info("[compareLargeImagesBySlice] slice ที่ต่าง: $diffSlices / $sliceCount → $percentDiff%");

    return [
        'image1Hash' => $image1Hash,
        'image2Hash' => $image2Hash,
        'sliceDiffs' => $sliceDiffs,
        'percentDiff' => $percentDiff
    ];
}


public static function cropImage($path, $x, $y, $w, $h)
{
    $img = self::createImageAuto($path);
    if (!$img) return false;

    $crop = imagecreatetruecolor($w, $h);
    imagecopy($crop, $img, 0, 0, $x, $y, $w, $h);
    imagedestroy($img);
    return $crop;
}



    private static function compareImageChunk($chunk1, $chunk2, $index): bool
    {
        try {
            $chunk1->resize(200, null, function ($constraint) {
                $constraint->aspectRatio();
            });

            $chunk2->resize(200, null, function ($constraint) {
                $constraint->aspectRatio();
            });

            $hash1 = md5($chunk1->encode('png'));
            $hash2 = md5($chunk2->encode('png'));

            return $hash1 === $hash2;
        } catch (\Exception $e) {
            Log::error("[compareImageChunk] slice#$index error: " . $e->getMessage());
            return false;
        }
    }

    public static function createImageAuto($path)
    {
        if (!file_exists($path)) {
            Log::error("[createImageAuto] ไม่พบไฟล์: $path");
            return false;
        }

        $info = getimagesize($path);
        $mime = $info['mime'] ?? '';

        switch ($mime) {
            case 'image/jpeg':
                return imagecreatefromjpeg($path);
            case 'image/png':
                return imagecreatefrompng($path);
            case 'image/gif':
                return imagecreatefromgif($path);
            case 'image/webp':
                return imagecreatefromwebp($path);
            default:
                Log::error("[createImageAuto] ไม่รองรับ MIME: $mime → $path");
                return false;
        }
    }
}
