<?php
//app_path() . "\\Console\\Commands\\temp\\youtube.png";
define('PATH_CAPTURE_SCREEN_MASTER', __DIR__ . '/../screen-master/');
define('PATH_PHANTOM_JS',  __DIR__ . '/../screen-master');
include_once  __DIR__ . '/../screen-master-v2/autoload.php';


class DownloadImage
{
    public function download($serverUrl, $imageName, $Delay)
    {
        // 1) เตรียมโฟลเดอร์ปลายทาง
        $dir = dirname($imageName);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        // 2) ตั้งค่าเบื้องต้น
        $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0 Safari/537.36';
        $msDelay = max(0, (int)$Delay) * 1000; // ใช้ ms

        $screen = new \Screen\Capture();
        $screen->setUrl($serverUrl);

        // สำคัญ: ปิด web-security และเปิดโหลดรูป/ข้าม cert error
        $screen->setOptions([
            'ignore-ssl-errors' => 'yes',
            'ssl-protocol'      => 'any',
            'web-security'      => 'false',
            'load-images'       => 'true'
        ]);

        // viewport กว้าง/สูงเป็น int
        $screen->setWidth(1366);
        $screen->setHeight(3000);

        // อย่าตั้ง clip 0 — เสี่ยงโดนครอปเป็น 0×0
        // $screen->setClipWidth(0);
        // $screen->setClipHeight(0);

        $screen->setUserAgentString($ua);
        $screen->setBackgroundColor('#ffffff');
        $screen->setImageType('png');
        $screen->setDelay($msDelay); // รอ JS ให้เสร็จก่อนค่อย render

        // 3) cap ครั้งที่ 1
        $ok = $this->trySave($screen, $imageName);

        // 4) ถ้ายังเล็ก/ว่าง ให้ retry พร้อมดีเลย์เพิ่มและ UA เดิม
        if (!$ok || filesize($imageName) < 15 * 1024) { // <15KB ถือว่าเสี่ยง “ขาว”
            // รอเพิ่มอีก 5 วิ
            $screen->setDelay($msDelay + 5000);

            // บางเว็บต้อง viewport เล็กลงเพื่อหลบ layout ขาว — ลองปรับเล็กน้อย
            $screen->setWidth(1280);
            $screen->setHeight(2400);

            $this->trySave($screen, $imageName);
        }
    }

    private function trySave($screen, $path)
    {
        try {
            $screen->save($path);
            // double-check
            clearstatcache(true, $path);
            return file_exists($path) && filesize($path) > 0;
        } catch (\Exception $e) {
            // พิมพ์ error ทิ้งไว้ (หรือใช้ Log::error ก็ได้)
            echo "[capture] " . $e->getMessage() . "\n";
            return false;
        }
    }
}
