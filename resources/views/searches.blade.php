@extends('layouts.app')
@section('content')
<section id="content">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light">
            <div class="bc-head">@langapp('search_results_for_tag',['keyword' => $keyword])</div>
            <span class="pull-right" style="margin-top: 1.2rem;font-size: 16px;font-weight: bold;">
                Total Result : 0
            </span>
        </header>
        <section class="scrollable wrapper bg" id="clauses" style="padding: 8px !important">
            <div class="panel-group m-b" id="accordion2">
                <ul class="list no-style" id="clauses-list">

                    {{-- <li class="panel panel-default" id="clause-news">
                        <div class="panel-heading">
                            <a class="accordion-toggle name" data-toggle="collapse" data-parent="#accordion2" href="#{{ slugify('news') }}">
                                @icon('solid/caret-right') {{ humanize("news") }}({{$searchNews["count"]}})
                            </a>
                        </div>
                        <div id="{{ slugify('news') }}" class="panel-collapse collapse">
                            @foreach ($searchNews["news"] as $key => $value)
                                <div class="panel-body clause">
                                    <a href="#{{$value["id"]}}">
                                        {{$value["name"]}}
                                    </a>
                                    <div class='text-ellipsis'>{{$value["content"]}}</div>
                                </div>
                            @endforeach
                        </div>
                    </li> --}}
                    @if (!empty($dataSearch))
                        @foreach ($dataSearch as $key => $value)
                            @if (isset($value["count"])&&$value["count"] > 0)
                                <li class="panel panel-default">
                                    <div class="panel-heading fontw-weight-bold d-none">
                                        <a class="accordion-toggle name" data-toggle="collapse" data-parent="#accordion2" href="#{{ slugify($key) }}">
                                            @icon('solid/caret-right') {{ humanize($key) }} ({{$value["count"]}})
                                        </a>
                                    </div>
                                    <div id="{{ slugify($key) }}" class="panel-collapse collapse in">

                                        @foreach ($value["queryData"] as $key2 => $value2)
                                            <div class="panel-body clause">
                                                @if ($key == "News")
                                                <div class="pull-left m-r-xs">
                                                    <span class="badges-search badges-news">
                                                        News
                                                    </span>
                                                </div>
                                                @elseif ($key == "Events")
                                                <div class="pull-left m-r-xs">
                                                    <span class="badges-search badges-event">
                                                        Event
                                                    </span>
                                                </div>
                                                @elseif ($key == "indicators")
                                                <div class="pull-left m-r-xs">
                                                    <span class="badges-search badges-indicator">
                                                        Indicator
                                                    </span>
                                                </div>
                                                @elseif ($key == "Web Defacement")
                                                <div class="pull-left m-r-xs">
                                                    <span class="badges-search badges-deface">
                                                        Web Defacement
                                                    </span>
                                                </div>
                                                @elseif ($key == "Vulnerabilities")
                                                <div class="pull-left m-r-xs">
                                                    <span class="badges-search badges-vul">
                                                        Vulnerabilities
                                                    </span>
                                                </div>
                                                @elseif ($key == "Compromised")
                                                <div class="pull-left m-r-xs">
                                                    <span class="badges-search badges-compro">
                                                        Compromised
                                                    </span>
                                                </div>
                                                @elseif ($key == "Data Leak")
                                                <div class="pull-left m-r-xs">
                                                    <span class="badges-search badges-dataleak">
                                                        Data Leak
                                                    </span>
                                                </div>
                                                @else
                                                <div class="pull-left m-r-xs">
                                                    <span class="badges-search badges-other">
                                                        Other
                                                    </span>
                                                </div>
                                                @endif
                                                <a href="{{$value2["link"]}}" target="_blank">
                                                    {{$value2["name"]}}
                                                </a>
                                                <div style="
                                                max-height:100px;
                                                overflow:hidden;
                                                text-overflow: ellipsis;
                                                -webkit-box-orient: vertical;">{!!$value2["content"]!!}</div>
                                            </div>
                                        @endforeach
                                        
                                        @if ($key == "Events" && $value["count"] > 100)
                                            <div class="panel-body clause">
                                                <a href="{{$value["moreDetail"]}}" target="_blank">
                                                    กดเพื่อดูเพิ่มเติม
                                                </a>
                                                <div style="
                                                max-height:100px;
                                                overflow:hidden;
                                                text-overflow: ellipsis;
                                                -webkit-box-orient: vertical;"></div>
                                            </div>

                                        @endif
                                    </div>
                                </li>
                            @endif
                        @endforeach
                    @else
                        <div class="notfound">
                            <img src="{{asset('images/notfound.png')}}" alt="">
                            <h1>Sorry. no result found</h1>
                            <p>What you searched was unfortunately <br>not found or doesn't exist.</p>
                        </div>
                    @endif
                    {{-- @foreach (Modules\Contracts\Entities\Clause::orderBy('id', 'desc')->get() as $clause)
                    <li class="panel panel-default" id="clause-{{ $clause->id }}">
                        <div class="panel-heading">
                            <a class="accordion-toggle name" data-toggle="collapse" data-parent="#accordion2" href="#{{ slugify($clause->name) }}">
                                @icon('solid/caret-right') {{ humanize($clause->name) }}
                            </a>
                        </div>
                        <div id="{{ slugify($clause->name) }}" class="panel-collapse collapse">
                            <div class="panel-body clause">
                                @parsedown($clause->clause)
                            </div>
                        </div>
                    </li>
                    @endforeach --}}
                </ul>   
            </div>
        </section>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
</section>
@push('pagestyle')

@endpush
@endsection

