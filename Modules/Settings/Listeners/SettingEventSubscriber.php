<?php

namespace Modules\Settings\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Settings\Queue\ShouldQueue;

class SettingEventSubscriber
{

    /**
     * Setting enabled listener
     */
    public function onSettingUpdated($event)
    {
        if (!is_null($event->user)) {
            $data = [
                'action' => 'activity_update_setting', 'icon' => 'fa-gears', 'user_id' => $event->user->id,
                'value1' => '', 'value2'   => ''
            ];
            $event->user->activities()->create($data);
        }
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Modules\Settings\Events\SettingUpdated',
            'Modules\Settings\Listeners\SettingEventSubscriber@onSettingUpdated'
        );
    }
}
