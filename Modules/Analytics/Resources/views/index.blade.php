@extends('layouts.app')

@section('content')


<section id="content">
    <section class="hbox stretch">

            <section class="vbox">
                <section class="scrollable wrapper bg">
                    <section class="panel panel-default">

                        <header class="panel-heading">

                            @include('analytics::report_header')

                        </header>

                        <div class="panel-body">

                            <div class="alert alert-info">
                                <button type="button" class="close" data-dismiss="alert">×</button>
                                @icon('solid/info-circle') @langapp('amount_displayed_in_your_cur')  &nbsp;<span
                                        class="label label-success">{{  get_option('default_currency')  }}</span>
                            </div>




                            @include('analytics::_'.$module.'.dashboard')

                            

                            


                        </div>

                    </section>
                </section>


            </section>

    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
</section>

@endsection
