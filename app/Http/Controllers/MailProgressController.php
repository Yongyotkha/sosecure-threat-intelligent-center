<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MailProgressController extends Controller
{
    public function __invoke(Request $request, int $pct, string $label)
    {
        $pct  = max(0, min(100, (int)$pct));
        $size = (int) $request->query('size', 160);
        $t    = (int) $request->query('t', 14);

        $im = imagecreatetruecolor($size, $size);
        imagesavealpha($im, true);
        $trans = imagecolorallocatealpha($im, 0, 0, 0, 127);
        imagefill($im, 0, 0, $trans);

        $gray  = imagecolorallocate($im, 229, 231, 235);
        $green = imagecolorallocate($im, 22, 163, 74);
        $white = imagecolorallocate($im, 255, 255, 255);

        $outer = $size - 4;
        imagefilledellipse($im, $size/2, $size/2, $outer, $outer, $gray);

        if ($pct > 0) {
            $end = -90 + (360 * $pct / 100);
            imagefilledarc($im, $size/2, $size/2, $outer, $outer, -90, $end, $green, IMG_ARC_PIE);
        }

        $inner = $outer - (2 * $t);
        imagefilledellipse($im, $size/2, $size/2, $inner, $inner, $white);

        ob_start();
        imagepng($im);
        imagedestroy($im);
        $pngData = ob_get_clean();

        return response($pngData, 200)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
    }
}
