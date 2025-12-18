<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class WebDefacementsScreenshotCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:WebDefacementsScreenshotCheck {url} {port} {site_id} {url_id} {delay}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'WebDefacementsScreenshotCheck (mShots)';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $url     = (string) $this->argument('url');
        $port    = $this->argument('port'); // Unused
        $site_id = (int) $this->argument('site_id');
        $url_id  = (int) $this->argument('url_id');
        if ($url_id === 0) {
            $url_id = random_int(10, 100);
        }

        $delayMs = (int) $this->argument('delay');
        $result = [];

        try {
            // validate url
            if (!$this->is_url($url)) {
                $result["Result"] = 0;
                $result["message"] = "The url is not formatted.";
                $this->line(json_encode($result));
                return 1;
            }

            // Path settings
        $publicDir = base_path() . "/public/images/webdefacment_mages/{$site_id}/{$url_id}";
        
        // Logic: Check if original exists
        $filenameOriginal = "image_original.png";
        $pathOriginal = $publicDir . "/" . $filenameOriginal;
        
        if (File::exists($pathOriginal)) {
            // Original exists, save as Current/Check
            $targetFilename = "{$site_id}_{$url_id}_Defacement_Now.png";
        } else {
            // No original, save as Original
            $targetFilename = $filenameOriginal;
        }

        $fileFull = $publicDir . "/" . $targetFilename;
        $webPath  = "/images/webdefacment_mages/{$site_id}/{$url_id}/{$targetFilename}";

            // Create directory (Compatible check)
            if (!File::exists($publicDir)) {
                File::makeDirectory($publicDir, 0755, true);
            }

            // mShots URL
            $width = 1366;
            $mshotsUrl = "https://s.wordpress.com/mshots/v1/" . rawurlencode($url) . "?w={$width}";

            // Retry settings
            $maxAttempts = 3;
            $timeoutSec  = 60;
            
            $imageBytes = null;
            $lastErr = null;

            // Feedback: Start
            Log::info("Starting screenshot capture for: {$url}");

            // Pre-warm: Trigger mShots generation
            try {
                $client = new Client(['timeout' => 10, 'verify' => false]);
                $client->request('HEAD', $mshotsUrl, [
                   'headers' => ['User-Agent' => 'Mozilla/5.0']
                ]);
                Log::info("Triggered generation...");
            } catch (\Throwable $t) {
                // Ignore pre-warm errors
            }

            // Initial wait for generation (e.g. 3 seconds)
            sleep(3);

            for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                Log::info("Attempt {$attempt}/{$maxAttempts}...");
                try {
                    $client = new Client([
                        'timeout' => $timeoutSec,
                        'verify' => false, // sometimes SSL issues cause silence/fail
                        'headers' => [
                            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120 Safari/537.36',
                        ]
                    ]);

                    $response = $client->request('GET', $mshotsUrl);
                    $body = (string) $response->getBody();

                    // Check status and size (increase size check to avoid small placeholders)
                    if ($response->getStatusCode() === 200 && strlen($body) > 3000) {
                        $ct = strtolower(implode(';', $response->getHeader('Content-Type')));
                        if (str_contains($ct, 'image/')) {
                            $imageBytes = $body;
                            Log::info("Success! Image size: " . strlen($body) . " bytes.");
                            break;
                        }
                    }

                    $lastErr = "HTTP 200 but invalid content or too small (<3KB) (attempt {$attempt})";
                    Log::error($lastErr);

                } catch (\Throwable $e) {
                    $lastErr = $e->getMessage();
                    Log::error("Error: " . $lastErr);
                }

                // Wait before retry
                if ($attempt < $maxAttempts) {
                    $wait = ($delayMs > 0) ? $delayMs : 3000;
                    Log::info("Waiting " . ($wait/1000) . "s before retry...");
                    usleep($wait * 1000);
                }
            }

            if ($imageBytes === null) {
                $result["Result"] = 0;
                $result["message"] = "Screenshot failed (mShots). " . ($lastErr ?? "Unknown error");
                $this->line(json_encode($result));
                return 1;
            }

            // Save file
            File::put($fileFull, $imageBytes);

            $result["Result"] = 1;
            $result["image_url"] = $webPath;
            $result["message"] = "";
            $result["image_path_original_full"] = $fileFull;
            $result["image_path_original"] = "/public" . $webPath;
            $result["url_id"] = $url_id;

            $this->line(json_encode($result));
            return 0;

        } catch (\Throwable $e) {
            $result["Result"] = 0;
            $result["message"] = "Screenshot failed: " . $e->getMessage();
                $this->line(json_encode($result));
            return 1;
        }
    }

    // แปลงจาก function เดิมให้เป็น method ของ class (จะได้เรียก $this->is_url ได้)
    public function is_url($uri)
    {
        if (preg_match('/^(http|https):\\/\\/[a-z0-9_]+([\\-\\.]{1}[a-z_0-9]+)*\\.[_a-z]{2,5}((:[0-9]{1,5})?\\/.*)?$/i', $uri)) {
            return $uri;
        }
        return false;
    }
}
