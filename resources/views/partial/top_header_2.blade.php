<header class="bg-{{ get_option('top_bar_color') }} header navbar navbar-fixed-top-xs nav-z">
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="btn btn-link visible-xs" data-toggle="class:nav-off-screen" data-target="#nav">
                @icon('solid/bars')
            </a>
            <a href="{{  url('/')  }}" class="navbar-brand">
                <img src="{{asset('images/logo_threat/logo.png')}}" class="m-r-sm" onerror="setDefaultPic(this)">
            </a>
            <a class="btn btn-link visible-xs" data-toggle="dropdown" data-target=".nav-user">
                @icon('solid/cog')
            </a>
        </div>

    </div>
</header>

@push('pagescript')
    <script>
        $('.btn-header-lookup').click(function(){
            let keyword = $('#search_input').val();
            window.location.href = '/search?keyword=' + keyword + '&mode=lookup';
        })
    </script>
@endpush
