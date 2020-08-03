<?php

namespace Modules\Teams\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AssignmentEventSubscriber
{
    protected $user;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $this->user = \Auth::id() ?? 1;
    }

    /**
     * Assignment Deleted
     */
    public function onAssignmentDeleted($event)
    {
        $event->assignment->assignable->activities()->create(
            [
            'action' => 'activity_team_removed', 'icon' => 'fa-user-slash', 'user_id' => $this->user,
            'value1' => $event->assignment->user->name, 'value2'   => $event->assignment->assignable->name,
            ]
        );
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'Modules\Teams\Events\AssignmentDeleted',
            'Modules\Teams\Listeners\AssignmentEventSubscriber@onAssignmentDeleted'
        );
    }
}
