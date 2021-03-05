<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [

    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     */
    protected function schedule(Schedule $schedule)
    {
        // Uncomment for shared hosting



        // $schedule->command('app:FeedCompromisedServer')->cron('0 6 * * *')->withoutOverlapping(5);
      
        // $schedule->command('app:OTXMDFeedIndicator')->cron('0 */12 * * *')->withoutOverlapping(5);
        // $schedule->command('app:OTXMDFeedPulse')->cron('0 */12 * * *')->withoutOverlapping(5);
        // $schedule->command('app:OTXMDFeedType')->cron('0 0 1 * *')->withoutOverlapping(5);

        // $schedule->command('app:RSS_Feed')->cron('0 */1 * * *')->withoutOverlapping(5);
        // $schedule->command('app:OTXMDFeedIndicator')->cron('0 */12 * * *')->withoutOverlapping(5);
        // $schedule->command('app:OTXMDFeedPulse')->cron('0 */12 * * *')->withoutOverlapping(5);
        // $schedule->command('app:OTXMDFeedType')->cron('0 0 1 * *')->withoutOverlapping(5);
        //$schedule->command('app:OTXFeedType')->cron('0 */1 * * *')->withoutOverlapping(5);
        //$schedule->command('app:OTXFeedData')->cron('0 */1 * * *')->withoutOverlapping(5);
      
        // $schedule->command('queue:work --workicedaemon --queue=default,high,normal,low --tries=3')->everyMinute()->withoutOverlapping(5);
        // $schedule->command('backup:clean')->dailyAt('02:00')->name('backup.cleaner')->withoutOverlapping(5);
        // $schedule->command('backup:run')->dailyAt('03:00')->name('backup.runner')->withoutOverlapping(5);
        // $schedule->command('backup:monitor')->dailyAt('03:05')->name('backup.monitor')->withoutOverlapping(5);
        // $schedule->command('app:updates')->dailyAt('02:30')->name('updates.checker')->withoutOverlapping(5);
        // $schedule->command('app:license')->dailyAt('01:45')->name('license.checker')->withoutOverlapping(5);
        // $schedule->command('app:balances')->dailyAt('01:05')->name('balances.compute')->withoutOverlapping(5);
        // $schedule->command('analytics:compute')->hourlyAt(15)->name('analytics.compute')->withoutOverlapping(5);
        // $schedule->command('tickets:autoclose')->hourlyAt(30)->name('tickets.closer')->withoutOverlapping(5);
        // $schedule->command('tickets:emails')->everyTenMinutes()->name('tickets.emails')->withoutOverlapping(5);
        // $schedule->command('tickets:feedback')->dailyAt('01:00')->name('tickets.feedback')->withoutOverlapping(5);
        // $schedule->command('contacts:emails')->everyTenMinutes()->name('contacts.emails')->withoutOverlapping(5);
        // $schedule->command('deals:forecast')->hourlyAt(10)->name('deals.forecast')->withoutOverlapping(5);
        // $schedule->command('projects:monitor')->hourlyAt(20)->name('projects.monitor')->withoutOverlapping(5);
        // $schedule->command('deals:velocity')->dailyAt('02:15')->name('deals.sales-velocity')->withoutOverlapping(5);
        // $schedule->command('leads:emails')->everyTenMinutes()->name('leads.emails')->withoutOverlapping(5);
        // $schedule->command('app:xrates')->dailyAt('01:20')->name('exchange.rates')->withoutOverlapping(5);
        // $schedule->command('app:reminders')->everyThirtyMinutes()->name('reminders')->withoutOverlapping(5);
        // $schedule->command('app:recurring')->dailyAt('02:10')->name('recurring')->withoutOverlapping(5);
        // $schedule->command('invoices:reminders')->dailyAt('00:30')->name('invoices.reminder')->withoutOverlapping(5);
        // $schedule->command('app:daily-digest')->dailyAt(config('system.daily_digest.send_at'))->name('daily.digest');
        // $schedule->command('app:activities')->dailyAt('02:45')->name('clean.activity');
        // $schedule->command('log:clear --keep-last')->dailyAt('02:50')->name('logs.cleaner')->withoutOverlapping(5);
        // $schedule->command('app:cleancsv')->dailyAt('03:30')->name('csv.cleaner')->withoutOverlapping(5);
        // $schedule->command('queue:flush')->dailyAt('03:45')->name('queue.flush')->withoutOverlapping(5);
        // $schedule->command('app:lang')->dailyAt('03:50')->name('lang.progress')->withoutOverlapping(5);
        // $schedule->command('cache:gc')->dailyAt('03:45')->name('cache.garbage')->withoutOverlapping(5);
        // $schedule->command('app:gdpr-delete')->dailyAt('01:45')->name('gdpr.delete')->withoutOverlapping(5);
        // $schedule->command('app:reset-demo')->cron('0 */3 * * *')->name('demo.reset')->withoutOverlapping(5);
        // $schedule->command('inspire')
        //          ->hourly();
      $delay_WebDefacementProccess = rand(1,55);
      $schedule->command('app:WebDefacementProccess')->everyMinute();
      $schedule->command('transaction:ssh')->everyMinute()->withoutOverlapping(5);
      $schedule->command('transaction:saveScan')->everyMinute()->withoutOverlapping(5);
      
      $schedule->command('app:RSS_Feed')->cron('0 */1 * * *')->withoutOverlapping(5);
      $schedule->command('app:news_permission')->cron('0 */6 * * *')->withoutOverlapping(5);

      $schedule->command('app:MDCVEDataYear')->dailyAt('03:00')->name('lang.progress')->withoutOverlapping(5);
      $schedule->command('app:MDCVEBatchJob')->dailyAt('8:00')->name('lang.progress')->withoutOverlapping(5);

      $schedule->command('app:OTXMDFeedIndicator')->cron('0 */12 * * *')->withoutOverlapping(5);
      $schedule->command('app:OTXMDFeedPulse')->cron('0 */12 * * *')->withoutOverlapping(5);
      $schedule->command('app:OTXMDFeedType')->cron('0 0 1 * *')->withoutOverlapping(5);
      $schedule->command('app:OTXFeedType')->cron('0 */1 * * *')->withoutOverlapping(5);
      $schedule->command('app:OTXFeedData')->cron('0 */1 * * *')->withoutOverlapping(5);
      $schedule->command('app:MDMISPFeedDaily')->cron('0 */12 * * *')->withoutOverlapping(5);
      $schedule->command('app:data_leak_social')->hourly()->withoutOverlapping(5);
      $schedule->command('app:MDMISPFeedDaily')->hourly()->withoutOverlapping(5);
      $schedule->command('app:MDFeedDarkWeb')->dailyAt('03:45')->withoutOverlapping(5);
      $schedule->command('app:RemoveFileLogFromStorage')->cron('0 0 */2 * *')->withoutOverlapping(5);
      //$schedule->command('app:test_indicator_update_ref')->everyMinute()->withoutOverlapping(5);
      $schedule->command('rm -rf /var/www/html/insight.sosecure.co.th/threat-intelligent-center/public/screenshot/screen-master-v2/jobs/')->cron('0 */12 * * *')->withoutOverlapping(5);
      
    }

    /**
     * Register the Closure based commands for the application.
     */
    protected function commands()
    {
      include base_path('routes/console.php');
      $this->load(__DIR__ . '/Commands');
    }
  }
