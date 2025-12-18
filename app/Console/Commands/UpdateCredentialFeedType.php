<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\DataLeakFeed;
use Illuminate\Support\Facades\Log;

class UpdateCredentialFeedType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-credential-feed-type {--dry-run : Run without saving changes to see samples}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update feel_type in data_leak_feed from "credential" to "darkweb" or "surface_web" based on source_name';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $this->info('Starting update of credential feed types...' . ($isDryRun ? ' (DRY RUN)' : ''));

        // Fetch all DataLeakFeed where feel_type is 'credential'
        $feeds = DataLeakFeed::where('feel_type', 'credential')->get();

        if ($feeds->isEmpty()) {
            $this->info('No records found with feel_type = "credential".');
            return 0;
        }

        $countUpdated = 0;
        $countSurface = 0;
        $countDarkweb = 0;

        $samplesSurface = [];
        $samplesDarkweb = [];

        foreach ($feeds as $feed) {
            $sourceName = strtolower($feed->source_name ?? '');
            
            // Classification Logic
            if ($this->isSurfaceWeb($sourceName)) {
                $newType = 'surface_web';
                $countSurface++;
                if (count($samplesSurface) < 10) {
                    $samplesSurface[] = $feed->source_name;
                }
            } else {
                $newType = 'darkweb'; 
                $countDarkweb++;
                if (count($samplesDarkweb) < 10) {
                    $samplesDarkweb[] = $feed->source_name;
                }
            }

            if (!$isDryRun && $feed->feel_type !== $newType) {
                $feed->feel_type = $newType;
                $feed->save();
                $countUpdated++;
            }
        }

        if ($isDryRun) {
            $this->info("\n--- PREVIEW RESULTS ---");
            $this->info("Total Records to Process: " . $feeds->count());
            $this->info("Classified as Surface Web: $countSurface");
            $this->info("Classified as Dark Web: $countDarkweb");
            
            $this->info("\n[Sample Surface Web Sources]");
            foreach ($samplesSurface as $s) $this->line(" - $s");

            $this->info("\n[Sample Dark Web Sources]");
            foreach ($samplesDarkweb as $s) $this->line(" - $s");
        } else {
            $this->info("Completed. Updated: $countUpdated records.");
            $this->info("Classified as Surface Web: $countSurface");
            $this->info("Classified as Dark Web: $countDarkweb");
        }

        return 0;
    }

    /**
     * Determine if the source name belongs to Surface Web.
     *
     * @param string $sourceName
     * @return bool
     */
    private function isSurfaceWeb($sourceName)
    {
        // 1. If it contains '.onion', it is definitely Dark Web (not Surface).
        if (strpos($sourceName, '.onion') !== false) {
            return false; 
        }

        // 2. Check explicit keywords (Social, Shopping, Etc.)
        $surfaceKeywords = [
            'facebook',
            'twitter',
            'linkedin',
            'instagram',
            'youtube',
            'gmail',
            'yahoo',
            'hotmail',
            'outlook',
            'amazon',
            'ebay',
            'paypal',
            'netflix',
            'uber',
            'grab',
            'vk.com',
            'ok.ru',
            'weibo',
            'telegram',
            'discord',
            'line',
            'whatsapp',
            'pinterest',
            'tumblr',
            'reddit',
            'tiktok',
            'snapchat',
            'twitch',
            'skype',
            'viber',
            'wechat',
            'messenger',
            // Dev / Work
            'github',
            'gitlab',
            'bitbucket',
            'stackoverflow',
            'trello',
            'slack',
            'zoom',
            'microsoft',
            'apple',
            'google',
            'dropbox',
            'adobe',
            // Shopping
            'shopee',
            'lazada',
            'alibaba',
            'aliexpress',
            // Thai specific
            'pantip',
            'sanook',
            'kapook',
            'dek-d',
            'mthai',
            'wongnai',
            'kaidee',
            'blockdit',
            'trueid',
        ];

        foreach ($surfaceKeywords as $keyword) {
            if (strpos($sourceName, $keyword) !== false) {
                return true;
            }
        }

        // 3. Logic: If it looks like a domain (has a dot and typical TLD), it's likely Surface Web.
        // e.g. "something.com", "shop.co.th"
        // But exclude simple filenames or versions if possible. 
        // Simple regex for domain-like string:
        // At least one dot, no spaces (usually), ends with 2-6 letters.
        if (preg_match('/^[a-z0-9.-]+\.[a-z]{2,10}$/i', $sourceName)) {
            return true;
        }

        // 4. Fallback: If it's just a name like "Collection #1", "AntiPublic", "Exploit.in" (no TLD),
        // we assume it's a Breach Compilation / Dark Web source.
        return false;
    }
}
