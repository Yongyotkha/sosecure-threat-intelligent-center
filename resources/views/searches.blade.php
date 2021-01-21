@extends('layouts.app')
@section('content')
<section id="content">
    <section class="hbox stretch">
        <aside>
            <section class="vbox bg">
                <header class="header bg-white b-b clearfix hidden-print">
                    <div class="bc-head">@langapp('search_results_for_tag',['keyword' => $keyword])</div>
                </header>
                <div class="scrollable wrapper">
                    <div class="row m-b">
                        <div class="col-lg-12">
                            <section class="scrollable wrapper bg" id="clauses">
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
                                                        <div class="panel-heading">
                                                            <a class="accordion-toggle name" data-toggle="collapse" data-parent="#accordion2" href="#{{ slugify($key) }}">
                                                                @icon('solid/caret-right') {{ humanize($key) }} ({{$value["count"]}})
                                                            </a>
                                                        </div>
                                                        <div id="{{ slugify($key) }}" class="panel-collapse collapse">

                                                            @foreach ($value["queryData"] as $key2 => $value2)
                                                                <div class="panel-body clause">
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
                                            Notfound
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
                        </div>
                    </div>
                </div>
            </section>
        </aside>
        
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
</section>
@push('pagestyle')

@endpush
@endsection

