<?php

namespace Modules\Timetracking\Http\Controllers\Base;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Timetracking\Entities\TimeEntry;

class TimerController extends Controller
{
    protected $timer;
    protected $request;
    /**
     * Create a new controller instance.
     */
    public function __construct(TimeEntry $timer, Request $request)
    {
        $this->middleware(['auth', 'verified', '2fa']);
        $this->timer   = $timer;
        $this->request = $request;
    }

    public function view(TimeEntry $entry)
    {
        $data['entry'] = $entry;

        return view('timetracking::view')->with($data);
    }

    public function create($module = null, $id = null)
    {
        $data['module'] = $module;
        $data['id']     = $id;

        return view('timetracking::create')->with($data);
    }

    public function edit(TimeEntry $entry)
    {
        $data['entry'] = $entry;

        return view('timetracking::update')->with($data);
    }

    public function timers()
    {
        return view('timetracking::timers');
    }

    public function start($id, $module)
    {
        $model = classByName($module)->findOrFail($id);
        if (!$model->startClock()) {
            toastr()->error(langapp('timer_already_started'), langapp('response_status'));
            return redirect(url()->previous());
        }
        toastr()->info(langapp('timer_started_success'), langapp('response_status'));
        \Cache::forget('running-timers-' . \Auth::id());

        return redirect(url()->previous());
    }
    public function stop($id, $module)
    {
        $model = classByName($module)->findOrFail($id);
        if (!$model->stopClock()) {
            toastr()->error(langapp('timer_not_allowed'), langapp('response_status'));
            return redirect(url()->previous());
        }
        toastr()->info(langapp('timer_stopped_success'), langapp('response_status'));

        \Cache::forget('running-timers-' . \Auth::id());

        return redirect(url()->previous());
    }

    public function bill(TimeEntry $entry)
    {
        $entry->update(['billable' => 1]);
        toastr()->info(langapp('changes_saved_successful'), langapp('response_status'));

        return redirect()->route('projects.view', ['id' => $entry->timeable->id, 'tab' => 'timesheets']);
    }
    public function unbill(TimeEntry $entry)
    {
        $entry->update(['billable' => 0]);
        toastr()->info(langapp('changes_saved_successful'), langapp('response_status'));

        return redirect()->route('projects.view', ['id' => $entry->timeable->id, 'tab' => 'timesheets']);
    }

    public function delete(TimeEntry $entry)
    {
        $data['entry'] = $entry;

        return view('timetracking::delete')->with($data);
    }
}
