<?php

namespace Modules\CategorySettings\Jobs;

use App;
use Auth;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\CategorySettings\Entities\CategorySettings;
// use Modules\CategorySettings\Events\CategorySettingsDeleted;

class BulkDeleteCategorySettings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Delete the job if its models no longer exist.
     *
     * @var bool
     */
    public $deleteWhenMissingModels = true;
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    public $arr;
    public $Category_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $arr, $CategorySettings)
    {
        $this->arr     = $arr;
        $this->Category_id = $CategorySettings;
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
            $model = CategorySettings::where('id', $u)->first();
            $model->delete();
            // event(new CategorySettingsDeleted($model, $this->Category_id));
        }

        // if (App::runningInConsole() && $this->user_id && !App::runningUnitTests()) {
        //     Auth::logout();
        // }
    }
}
