<header class="header bg-white b-b clearfix">
    <div class="row m-t-sm">
        <div class="col-sm-12 m-b-xs">
            
            <div class="m-b-sm">
                <div class="btn-group">
                    <button class="btn btn-{{ get_option('theme_color') }} btn-sm dropdown-toggle"
                    data-toggle="dropdown">@icon('solid/sort') @langapp('filter') <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="{{ route('projects.view', ['id' => $project->id, 'tab' => 'timesheets'])  }}">@langapp('all')</a>
                        </li>
                        <li>
                            <a href="{{ route('projects.view', ['id' => $project->id, 'tab' => 'timesheets', 'item' => 'billable']) }}">@langapp('billable')
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('projects.view', ['id' => $project->id, 'tab' => 'timesheets', 'item' => 'unbillable']) }}">@langapp('not_billable')
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('projects.view', ['id' => $project->id, 'tab' => 'timesheets', 'item' => 'billed']) }}">@langapp('billed')
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('projects.view', ['id' => $project->id, 'tab' => 'timesheets', 'item' => 'unbilled']) }}">@langapp('unbilled')
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="pull-right">
                    @if ($project->isTeam() || isAdmin())
                    <a href="{{  route('timetracking.create', ['module' => 'projects', 'id' => $project->id])  }}"
                        data-toggle="ajaxModal" class="btn btn-{{ get_option('theme_color') }} btn-sm">
                        @icon('solid/plus') @langapp('create')
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </header>

    @php
        $entries = $project->timesheets;
        if(isset($item))
        {
            switch ($item) {
                case 'billable': $entries = $entries->where('billable', 1); break;
                case 'unbillable': $entries = $entries->where('billable', 0); break;
                case 'billed': $entries = $entries->where('billed', 1); break;
                case 'unbilled': $entries = $entries->where('billed', 0); break;
                default: break;
            }
        }
    @endphp

    <div class="table-responsive">
        <table id="timesheet-table" class="table table-striped">
            <thead>
                <tr>
                    <th class="hide"></th>
                    <th class="">@langapp('name')  </th>
                    <th class="">@langapp('user')</th>
                    <th class="">@langapp('total_time')  </th>
                    <th class="col-date">@langapp('start')  </th>
                    <th class="col-date">@langapp('stop')  </th>
                    <th class="col-date">@langapp('date')  </th>
                    <th class="no-sort"></th>
                </tr>
            </thead>
            <tbody>  
                @foreach ($entries as $key => $entry)
                <tr>
                    <td class="display-none">{{ $entry->id }}</td>
                    <td>
                        <a href="{{  route('timetracking.view', ['id' => $entry->id])  }}" data-toggle="ajaxModal" class="text-ellipsis">
                            {!! $entry->billed === 1 ? '<span class="text-success">✔</span>' : '<span class="text-danger">✘</span>' !!}
                            {{ $entry->task_id > 0 ? str_limit($entry->task->name, 25) : str_limit($entry->timeable->name, 25) }}
                        </a>
                    </td>
                    <td><span class="text-info">{{ str_limit($entry->user->name,10) }}</span></td>
                    <td>
                        @if ($entry->is_started == 1)
                        <label class="label label-danger">@icon('solid/sync-alt', 'fa-spin')</label>
                        @else
                        <i class="fas fa-{{ $entry->billable == 1 ? 'check-circle' : 'times-circle text-danger' }}"></i> {{ secToHours($entry->worked) }}
                        @endif
                    </td>
                    <td class="text-muted">
                        @if ($entry->start > 0)
                        {{  dateTimeFormatted( dateFromUnix($entry->start) )  }}
                        @endif
                    </td>
                    <td class="text-muted">
                        @if ($entry->end > 0)
                        {{  dateTimeFormatted( dateFromUnix($entry->end) )  }}
                        @endif
                    </td>
                    <td class="text-muted">
                        {{  dateFormatted($entry->created_at)  }}
                    </td>
                    
                    <td>
                        @if (isAdmin() || $entry->user_id == Auth::id())
                        @if ($entry->billable === 0)
                        <a href="{{  route('timetracking.bill', ['id' => $entry->id])  }}"
                            data-toggle="tooltip" data-title="@langapp('billable')  " data-placement="left" class="m-r-sm">
                            @icon('solid/clock', 'text-success')
                        </a>
                        @else
                        <a href="{{  route('timetracking.unbill', ['id' => $entry->id])  }}"
                            data-toggle="tooltip" data-title="@langapp('not_billable')  " data-placement="left" class="m-r-sm">
                            @icon('solid/clock', 'text-danger')
                        </a>
                        @endif
                        @if(!$entry->is_started)
                        <a href="{{  route('timetracking.edit', ['id' => $entry->id])  }}"
                            data-toggle="ajaxModal" data-rel="tooltip" title="@langapp('make_changes')" data-placement="left" class="m-r-xs">
                            @icon('solid/pencil-alt')
                        </a>
                        <a href="{{  route('timetracking.delete', ['id' => $entry->id])  }}" data-toggle="ajaxModal" data-rel="tooltip" title="@langapp('delete')" data-placement="left">
                            @icon('solid/trash-alt')
                        </a>
                        @endif
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @push('pagestyle')
        @include('stacks.css.datatables')
    @endpush
    @push('pagescript')
    @include('stacks.js.datatables')
    <script>
    $(function() {
    $('#timesheet-table').DataTable({
        processing: true,
        order: [[ 0, "desc" ]],
    });
    });
    </script>
    @endpush