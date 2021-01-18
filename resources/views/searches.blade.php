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
                                        @foreach (Modules\Contracts\Entities\Clause::orderBy('id', 'desc')->get() as $clause)
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
                                        @endforeach
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

