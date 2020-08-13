@extends('layouts.app')
@section('content')
<section id="content" class="bg">
    <section class="vbox">
        <header class="header panel-heading bg-white b-b b-light">
            <a href="{{route('alert.index')}}" class="btn btn-{{ get_option('theme_color') }} btn-sm btn-responsive pull-left m-r-5">
                @icon('solid/arrow-left')
            </a>
            <div class="bc-head">@langapp('alert') > Ut molestias et assumenda</div>
        </header>
        <section class="scrollable wrapper bg-white">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-ms-12">
                        <p>
                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Expedita repudiandae ratione in modi iusto, a ut eaque corrupti saepe doloribus cum. Delectus esse optio
                            at aspernatur possimus, repudiandae libero dolorem.
                        </p>
                        <p>
                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Expedita repudiandae ratione in modi iusto, a ut eaque corrupti saepe doloribus cum. Delectus esse optio
                            at aspernatur possimus, repudiandae libero dolorem.
                        </p>
                        <p>
                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Expedita repudiandae ratione in modi iusto, a ut eaque corrupti saepe doloribus cum. Delectus esse optio
                            at aspernatur possimus, repudiandae libero dolorem.
                        </p>
                        <p>
                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Expedita repudiandae ratione in modi iusto, a ut eaque corrupti saepe doloribus cum. Delectus esse optio
                            at aspernatur possimus, repudiandae libero dolorem.
                        </p>
                        <p>
                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Expedita repudiandae ratione in modi iusto, a ut eaque corrupti saepe doloribus cum. Delectus esse optio
                            at aspernatur possimus, repudiandae libero dolorem.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </section>
    <a href="#" class="hide nav-off-screen-block" data-toggle="class:nav-off-screen" data-target="#nav"></a>
</section>

@push('pagestyle')
    @include('stacks.css.datatables')
@endpush

@push('pagescript')
@include('stacks.js.datatables')

<script>

</script>
@endpush
@endsection