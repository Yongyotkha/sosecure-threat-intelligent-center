<?php

namespace App\Console\Commands;

class compareImages
{
    public $source = null;
    private $hasString = '';

    function __construct($source)
    {
        $this->source = $source;
    }

    private function mimeType($i)
    {
        /*returns array with mime type and if its jpg or png. Returns false if it isn't jpg or png*/
        $mime = getimagesize($i);
        $return = array($mime[0], $mime[1]);

        switch ($mime['mime']) {
            case 'image/jpeg':
                $return[] = 'jpg';
                return $return;
            case 'image/png':
                $return[] = 'png';
                return $return;
            default:
                return false;
        }
    }

    private function createImage($i)
    {
        /*retuns image resource or false if its not jpg or png*/
        $mime = $this->mimeType($i);
        if ($mime[2] == 'jpg') {
            return imagecreatefromjpeg($i);
        } else
            if ($mime[2] == 'png') {
            return imagecreatefrompng($i);
        } else {
            return false;
        }
    }

    private function resizeImage($source)
    {
        /*resizes the image to a 8x8 squere and returns as image resource*/
        $mime = $this->mimeType($source);
        $t = imagecreatetruecolor(8, 8);
        $source = $this->createImage($source);
        imagecopyresized($t, $source, 0, 0, 0, 0, 8, 8, $mime[0], $mime[1]);
        return $t;
    }

    private function colorMeanValue($i)
    {
        /*returns the mean value of the colors and the list of all pixel's colors*/
        $colorList = array();
        $colorSum = 0;
        for ($a = 0; $a < 8; $a++) {
            for ($b = 0; $b < 8; $b++) {
                $rgb = imagecolorat($i, $a, $b);
                $colorList[] = $rgb & 0xFF;
                $colorSum += $rgb & 0xFF;
            }
        }
        return array($colorSum / 64, $colorList);
    }

    private function bits($colorMean)
    {
        /*returns an array with 1 and zeros. If a color is bigger than the mean value of colors it is 1*/
        $bits = array();
        foreach ($colorMean[1] as $color) {
            $bits[] = ($color >= $colorMean[0]) ? 1 : 0;
        }
        return $bits;
    }

    public function compareWith($tagetImage)
    {
        $tagetString = $this->hasString($tagetImage);
        if ($tagetString) {
            return $this->compareHash($tagetString);
        }
        return 100;
    }

    /**
     * Hash String from image. You can save this string to database for reuse
     * @return String 64 character
     * */
    private function hasString($image)
    {
        $i1 = $this->createImage($image);
        if (!$i1) {
            return false;
        }
        $i1 = $this->resizeImage($image);
        imagefilter($i1, IMG_FILTER_GRAYSCALE);
        $colorMean1 = $this->colorMeanValue($i1);
        $bits1 = $this->bits($colorMean1);
        $result = '';
        for ($a = 0; $a < 64; $a++) {
            $result .= $bits1[$a];
        }
        return $result;
    }

    /**
     * Get current image hash String
     * */
    public function getHasString()
    {
        if ($this->hasString == '') {
            $this->hasString = $this->hasString($this->source);
        }
        return $this->hasString;
    }

    /**
     * Get hash String from image url
     * ex: $imageHash = $this->hasStringImage('http://media.com/image.jpg');
     * */
    public function hasStringImage($image)
    {
        return $this->hasString($image);
    }

    /**
     * Compare current image with an image hash String
     * @return int different rates . if different rates < 10 => duplicate image
     */
    public function compareHash($imageHash)
    {
        $sString = $this->getHasString();
        if (strlen($imageHash) == 64 && strlen($sString) == 64) {
            $diff = 0;
            $sString = str_split($sString);
            $imageHash = str_split($imageHash);
            for ($a = 0; $a < 64; $a++) {
                if ($imageHash[$a] != $sString[$a]) {
                    $diff++;
                }
            }
            return $diff;
        }
        return 64;
    }

    public function compareBySlices($targetImage, $sliceHeight = 1000)
    {
        $mime1 = $this->mimeType($this->source);
        $mime2 = $this->mimeType($targetImage);
        if (!$mime1 || !$mime2) return 100;

        list($width1, $height1) = $mime1;
        list($width2, $height2) = $mime2;

        $slices = min(floor($height1 / $sliceHeight), floor($height2 / $sliceHeight));
        $differentSlices = 0;

        for ($i = 0; $i < $slices; $i++) {
            $slice1 = imagecreatetruecolor($width1, $sliceHeight);
            $slice2 = imagecreatetruecolor($width2, $sliceHeight);

            $img1 = $this->createImage($this->source);
            $img2 = $this->createImage($targetImage);

            imagecopy($slice1, $img1, 0, 0, 0, $i * $sliceHeight, $width1, $sliceHeight);
            imagecopy($slice2, $img2, 0, 0, 0, $i * $sliceHeight, $width2, $sliceHeight);

            $temp1 = sys_get_temp_dir() . "/s1_{$i}.jpg";
            $temp2 = sys_get_temp_dir() . "/s2_{$i}.jpg";
            imagejpeg($slice1, $temp1);
            imagejpeg($slice2, $temp2);

            $hash1 = $this->hasString($temp1);
            $hash2 = $this->hasString($temp2);

            $diff = $this->compareHash($hash2);
            if ($diff > 10) $differentSlices++;

            imagedestroy($slice1);
            imagedestroy($slice2);
            unlink($temp1);
            unlink($temp2);
        }
        Log::info("Different slices: {$differentSlices} / {$slices} = " . round(($differentSlices / $slices) * 100, 2));

        return round(($differentSlices / $slices) * 100, 2);
    }

    /**
     * Compare source image ($this->source) with $targetImage using Pure PHP GD.
     * Calculates similarity % and draws red bounding boxes around changed regions.
     *
     * @param string $targetImage Path to the image to compare against
     * @param string $outputPath Path where highlighted diff image will be saved
     * @param int $threshold Minimum RGB color distance to treat pixel as changed (default 60)
     * @param float $similarityCutoff Similarity cutoff below which diff is flagged (default 98.0)
     * @return array
     */
    public function compareAndHighlight($targetImage, $outputPath, $threshold = 60, $similarityCutoff = 98.0)
    {
        $basePath = $this->source;
        if (!file_exists($basePath) || !file_exists($targetImage)) {
            return ['similarity' => 0, 'has_diff' => false, 'diff_boxes_count' => 0, 'diff_image' => null, 'error' => 'File not found'];
        }

        $img1 = $this->createImage($basePath);
        $img2 = $this->createImage($targetImage);

        if (!$img1 || !$img2) {
            return ['similarity' => 0, 'has_diff' => false, 'diff_boxes_count' => 0, 'diff_image' => null, 'error' => 'Invalid image format'];
        }

        $w1 = imagesx($img1); $h1 = imagesy($img1);
        $w2 = imagesx($img2); $h2 = imagesy($img2);

        $width = min($w1, $w2);
        $height = min($h1, $h2);

        $diffImg = imagecreatetruecolor($width, $height);
        imagecopy($diffImg, $img2, 0, 0, 0, 0, $width, $height);
        $red = imagecolorallocate($diffImg, 255, 0, 0);

        $blockSize = 35;
        $gridW = (int)ceil($width / $blockSize);
        $gridH = (int)ceil($height / $blockSize);
        $grid = array_fill(0, $gridW, array_fill(0, $gridH, false));

        $totalDiffPixels = 0;
        $totalSampled = 0;
        $step = 4;

        for ($gx = 0; $gx < $gridW; $gx++) {
            for ($gy = 0; $gy < $gridH; $gy++) {
                $startX = $gx * $blockSize;
                $startY = $gy * $blockSize;
                $endX = min($startX + $blockSize, $width);
                $endY = min($startY + $blockSize, $height);

                $cellDiffs = 0;
                for ($x = $startX; $x < $endX; $x += $step) {
                    for ($y = $startY; $y < $endY; $y += $step) {
                        $totalSampled++;
                        $rgb1 = imagecolorat($img1, $x, $y);
                        $rgb2 = imagecolorat($img2, $x, $y);

                        $r1 = ($rgb1 >> 16) & 0xFF; $g1 = ($rgb1 >> 8) & 0xFF; $b1 = $rgb1 & 0xFF;
                        $r2 = ($rgb2 >> 16) & 0xFF; $g2 = ($rgb2 >> 8) & 0xFF; $b2 = $rgb2 & 0xFF;

                        if (abs($r1 - $r2) + abs($g1 - $g2) + abs($b1 - $b2) > $threshold) {
                            $cellDiffs++;
                            $totalDiffPixels++;
                        }
                    }
                }

                if ($cellDiffs >= 3) {
                    $grid[$gx][$gy] = true;
                }
            }
        }

        $similarity = $totalSampled > 0 ? (1 - ($totalDiffPixels / $totalSampled)) * 100 : 100;
        $similarity = max(0, min(100, $similarity));

        $visited = array_fill(0, $gridW, array_fill(0, $gridH, false));
        $boxes = [];

        for ($gx = 0; $gx < $gridW; $gx++) {
            for ($gy = 0; $gy < $gridH; $gy++) {
                if ($grid[$gx][$gy] && !$visited[$gx][$gy]) {
                    $queue = [[$gx, $gy]];
                    $visited[$gx][$gy] = true;
                    $minGx = $gx; $maxGx = $gx;
                    $minGy = $gy; $maxGy = $gy;
                    $cellCount = 0;

                    while (!empty($queue)) {
                        list($cx, $cy) = array_shift($queue);
                        $cellCount++;
                        $minGx = min($minGx, $cx); $maxGx = max($maxGx, $cx);
                        $minGy = min($minGy, $cy); $maxGy = max($maxGy, $cy);

                        for ($dx = -1; $dx <= 1; $dx++) {
                            for ($dy = -1; $dy <= 1; $dy++) {
                                $nx = $cx + $dx; $ny = $cy + $dy;
                                if ($nx >= 0 && $nx < $gridW && $ny >= 0 && $ny < $gridH) {
                                    if ($grid[$nx][$ny] && !$visited[$nx][$ny]) {
                                        $visited[$nx][$ny] = true;
                                        $queue[] = [$nx, $ny];
                                    }
                                }
                            }
                        }
                    }

                    // Filter out isolated tiny noise blocks
                    if ($cellCount >= 2) {
                        $pxMinX = max(0, $minGx * $blockSize - 4);
                        $pxMinY = max(0, $minGy * $blockSize - 4);
                        $pxMaxX = min($width - 1, ($maxGx + 1) * $blockSize + 4);
                        $pxMaxY = min($height - 1, ($maxGy + 1) * $blockSize + 4);

                        $boxes[] = [$pxMinX, $pxMinY, $pxMaxX, $pxMaxY];
                    }
                }
            }
        }

        imagesetthickness($diffImg, 3);
        foreach ($boxes as $box) {
            imagerectangle($diffImg, $box[0], $box[1], $box[2], $box[3], $red);
        }

        $diffSaved = false;
        if (!empty($boxes) && $similarity < $similarityCutoff) {
            $outputDir = dirname($outputPath);
            if (!file_exists($outputDir)) {
                @mkdir($outputDir, 0777, true);
            }
            $ext = strtolower(pathinfo($outputPath, PATHINFO_EXTENSION));
            if ($ext === 'png') {
                imagepng($diffImg, $outputPath);
            } else {
                imagejpeg($diffImg, $outputPath, 85);
            }
            $diffSaved = true;
        } else {
            if (file_exists($outputPath)) {
                @unlink($outputPath);
            }
        }

        imagedestroy($img1);
        imagedestroy($img2);
        imagedestroy($diffImg);

        return [
            'similarity' => round($similarity, 2),
            'has_diff' => !empty($boxes) && $similarity < $similarityCutoff,
            'diff_boxes_count' => count($boxes),
            'diff_image' => $diffSaved ? $outputPath : null
        ];
    }
}

