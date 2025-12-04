<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Log;

class compareImages
{
    /** @var string path ของไฟล์ baseline (image_original.png) */
    public $source = null;

    /** @var string 64-bit aHash cache ของ $source */
    private $hasString = '';

    public function __construct($source)
    {
        $this->source = $source;
    }

    /* =========================
     *  Utilities (Safe I/O)
     * ========================= */

    /**
     * อ่านขนาด+mime ของรูปแบบปลอดภัย
     * @return array|false [width, height, 'jpg'|'png'] หรือ false ถ้าไม่ใช่/อ่านไม่ได้
     */
    private function mimeType($path)
    {
        if (!is_string($path) || $path === '' || !is_file($path) || !is_readable($path)) {
            Log::warning("mimeType: missing/unreadable -> {$path}");
            return false;
        }
        $info = @getimagesize($path);
        if ($info === false || !isset($info['mime'])) {
            Log::warning("mimeType: getimagesize failed -> {$path}");
            return false;
        }

        $ext = null;
        switch ($info['mime']) {
            case 'image/jpeg':
                $ext = 'jpg';
                break;
            case 'image/png':
                $ext = 'png';
                break;
            default:
                $ext = null;
        }
        if ($ext === null) {
            Log::warning("mimeType: unsupported mime {$info['mime']} -> {$path}");
            return false;
        }

        return array($info[0], $info[1], $ext);
    }

    /**
     * เปิดรูปเป็น GD resource
     * @return resource|false
     */
    private function createImage($path)
    {
        $mime = $this->mimeType($path);
        if ($mime === false) return false;

        try {
            if ($mime[2] === 'jpg') {
                return @imagecreatefromjpeg($path);
            } else { // png
                $im = @imagecreatefrompng($path);
                if ($im !== false) {
                    imagealphablending($im, true);
                    imagesavealpha($im, true);
                }
                return $im;
            }
        } catch (\Throwable $e) {
            Log::error("createImage: {$e->getMessage()} -> {$path}");
            return false;
        }
    }

    /**
     * บีบภาพเป็น 8x8 + grayscale สำหรับทำ aHash (รับ path หรือ resource)
     * @param string|resource $img
     * @return resource|false
     */
    private function makeGray8x8($img)
    {
        if (is_string($img)) {
            $src = $this->createImage($img);
            if ($src === false) return false;
            $m = $this->mimeType($img);
            $srcW = $m[0];
            $srcH = $m[1];
        } elseif (is_resource($img) && get_resource_type($img) === 'gd') {
            $src = $img;
            $srcW = imagesx($src);
            $srcH = imagesy($src);
        } else {
            return false;
        }

        $t = imagecreatetruecolor(8, 8);
        imagecopyresampled($t, $src, 0, 0, 0, 0, 8, 8, $srcW, $srcH);
        imagefilter($t, IMG_FILTER_GRAYSCALE);
        return $t;
    }

    /* =========================
     *  aHash Helpers
     * ========================= */

    private function colorMeanValue($i)
    {
        $list = array();
        $sum = 0;
        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x++) {
                $rgb = imagecolorat($i, $x, $y);
                $val = $rgb & 0xFF; // after grayscale, R=G=B
                $list[] = $val;
                $sum += $val;
            }
        }
        return array($sum / 64, $list);
    }

    private function bits($mean)
    {
        $bits = array();
        foreach ($mean[1] as $v) {
            $bits[] = ($v >= $mean[0]) ? 1 : 0;
        }
        return $bits;
    }

    /**
     * aHash 64 ตัวอักษรจาก path หรือ resource
     * @param string|resource $imageOrResource
     * @return string|false
     */
    private function hashString($imageOrResource)
    {
        $i1 = $this->makeGray8x8($imageOrResource);
        if ($i1 === false) return false;
        $mean = $this->colorMeanValue($i1);
        $bits = $this->bits($mean);
        imagedestroy($i1);
        return implode('', $bits);
    }

    // alias ให้โค้ดเก่าเรียกต่อได้
    private function hasString($imageOrResource)
    {
        return $this->hashString($imageOrResource);
    }

    /**
     * คืนค่า aHash ของ $this->source แบบ lazy
     */
    public function getHasString()
    {
        if ($this->hasString === '') {
            $this->hasString = $this->hasString($this->source);
        }
        return $this->hasString;
    }
    
    public function hasStringImage($image)
    {
        return $this->hasString($image);
    }

    /**
     * เทียบกับรูป target (เช่น current)
     * - ถ้า original (source) ไม่มี จะสร้างจาก target อัตโนมัติแล้วค่อยเทียบ
     */
    public function compareWith($targetImage)
    {
        // 1) ensure baseline original exists
        if (!$this->ensureBaselineFromCurrent($targetImage)) {
            return 100;
        }

        // 2) hash แล้วเทียบ
        $targetHash = $this->hasString($targetImage);
        if ($targetHash) {
            return $this->compareHash($targetHash);
        }
        return 100;
    }

    /**
     * เทียบ hash -> Hamming distance (0..64), ยิ่งน้อยยิ่งเหมือน
     */
    public function compareHash($imageHash)
    {
        $sString = $this->getHasString();
        if (is_string($imageHash) && strlen($imageHash) === 64 && is_string($sString) && strlen($sString) === 64) {
            $diff = 0;
            for ($i = 0; $i < 64; $i++) {
                if ($imageHash[$i] !== $sString[$i]) $diff++;
            }
            return $diff;
        }
        return 64;
    }

    /**
     * เปรียบเทียบแบบ slice (แนวตั้ง) แล้วคืน % สัดส่วน slice ที่ "ต่าง"
     * @return float 0..100 (100 = ล้มเหลว/ต่างหมด)
     */
    public function compareBySlices($targetImage, $sliceHeight = 1000)
    {
        // ensure baseline ก่อน
        if (!$this->ensureBaselineFromCurrent($targetImage)) {
            return 100.0;
        }

        $m1 = $this->mimeType($this->source);
        $m2 = $this->mimeType($targetImage);
        if ($m1 === false || $m2 === false) return 100.0;

        $w1 = $m1[0];
        $h1 = $m1[1];
        $w2 = $m2[0];
        $h2 = $m2[1];
        if ($h1 <= 0 || $h2 <= 0 || $w1 <= 0 || $w2 <= 0) {
            Log::warning("compareBySlices: invalid dims w1={$w1} h1={$h1} w2={$w2} h2={$h2}");
            return 100.0;
        }

        $img1 = $this->createImage($this->source);
        $img2 = $this->createImage($targetImage);
        if ($img1 === false || $img2 === false) {
            if (is_resource($img1)) imagedestroy($img1);
            if (is_resource($img2)) imagedestroy($img2);
            return 100.0;
        }

        $slices = min(max(1, (int)ceil($h1 / $sliceHeight)), max(1, (int)ceil($h2 / $sliceHeight)));
        $differentSlices = 0;

        for ($i = 0; $i < $slices; $i++) {
            $srcY1 = $i * $sliceHeight;
            $srcY2 = $i * $sliceHeight;

            $hSeg1 = min($sliceHeight, $h1 - $srcY1);
            $hSeg2 = min($sliceHeight, $h2 - $srcY2);
            if ($hSeg1 <= 0 || $hSeg2 <= 0) break;

            // slice1
            $slice1 = imagecreatetruecolor($w1, $hSeg1);
            imagecopy($slice1, $img1, 0, 0, 0, $srcY1, $w1, $hSeg1);

            // slice2 (ปรับสเกลให้เท่า slice1)
            $slice2src = imagecreatetruecolor($w2, $hSeg2);
            imagecopy($slice2src, $img2, 0, 0, 0, $srcY2, $w2, $hSeg2);

            if ($w2 !== $w1 || $hSeg2 !== $hSeg1) {
                $slice2 = imagecreatetruecolor($w1, $hSeg1);
                imagecopyresampled($slice2, $slice2src, 0, 0, 0, 0, $w1, $hSeg1, $w2, $hSeg2);
                imagedestroy($slice2src);
            } else {
                $slice2 = $slice2src;
            }

            // ทำ hash จาก resource โดยตรง
            $hash1 = $this->hasString($slice1);
            $this->hasString = $hash1 ? $hash1 : ''; // ตั้ง baseline ชั่วคราวให้ compareHash
            $hash2 = $this->hasString($slice2);

            $diff = $this->compareHash($hash2);

            if (is_resource($slice1)) imagedestroy($slice1);
            if (is_resource($slice2)) imagedestroy($slice2);

            if ($diff > 10) $differentSlices++; // threshold ปรับได้
        }

        if (is_resource($img1)) imagedestroy($img1);
        if (is_resource($img2)) imagedestroy($img2);

        $percent = round(($differentSlices / $slices) * 100, 2);
        Log::info("Different slices: {$differentSlices} / {$slices} = {$percent}%");

        return $percent;
    }

    /* =========================
     *  Baseline bootstrap
     * ========================= */

    /**
     * ถ้า original (source) ยังไม่มี ให้ "ก็อปจาก target" สร้าง baseline ก่อน
     * @return bool true=พร้อมใช้งาน baseline แล้ว
     */
    public function ensureBaselineFromCurrent($targetPath)
    {
        $orig = $this->source;

        // original พร้อมแล้ว
        if (is_file($orig) && is_readable($orig)) {
            return true;
        }

        // target ต้องพร้อมและเป็นรูปจริง
        $info = $this->mimeType($targetPath);
        if ($info === false) {
            Log::warning("ensureBaseline: target invalid -> {$targetPath}");
            return false;
        }

        // สร้างโฟลเดอร์
        $dir = dirname($orig);
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0755, true) && !is_dir($dir)) {
                Log::error("ensureBaseline: mkdir failed -> {$dir}");
                return false;
            }
        }

        // copy แบบ atomic
        $tmp = $orig . '.tmp-' . bin2hex(random_bytes(4));
        if (!@copy($targetPath, $tmp)) {
            @unlink($tmp);
            Log::error("ensureBaseline: copy to temp failed -> {$tmp}");
            return false;
        }
        if (!@rename($tmp, $orig)) {
            @unlink($tmp);
            Log::error("ensureBaseline: rename temp to original failed -> {$orig}");
            return false;
        }
        @chmod($orig, 0644);

        Log::info("ensureBaseline: created baseline from current", array(
            'original' => $orig,
            'current'  => $targetPath,
            'size'     => @filesize($orig) ? @filesize($orig) : 0,
        ));

        // reset cache ให้ hash ใหม่ของ original ถูกอ่านครั้งแรก
        $this->hasString = '';

        return true;
    }
}
