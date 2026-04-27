@extends('layouts.app')

@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light">
            <div class="bc-head">@langapp('settings') > IoC Feed</div>
        </header>

        <section class="scrollable wrapper">
            <div class="panel panel-default">
                <div class="panel-body">
                    <p>The IoC Feed API is synchronized and ready.</p>
                    <p>To manage your API tokens, please go to <strong>Site Settings > System</strong>.</p>
                </div>
            </div>
        </section>
    </section>
</section>
@endsection
