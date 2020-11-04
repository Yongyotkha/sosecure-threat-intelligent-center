<?php

namespace Modules\SiteSettings\Jobs;

use App;
use Auth;
use Modules\SiteSettings\Entities\Domain;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BulkDeleteDomainSettings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $deleteWhenMissingModels = true;
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    public $arr;
    public $domain_id;
    
    public function __construct(array $arr, $Domain)
    {
        $this->arr     = $arr;
        $this->domain_id = $Domain;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // if (App::runningInConsole() && $this->user_id && !App::runningUnitTests()) {
        //     Auth::onceUsingId($this->user_id);
        // }
        foreach ($this->arr as $u) {
            $model = Domain::where('id', $u)->first();
            $model->delete();
            // event(new CategorySettingsDeleted($model, $this->Category_id));
        }

        // if (App::runningInConsole() && $this->user_id && !App::runningUnitTests()) {
        //     Auth::logout();
        // }
    }
}
