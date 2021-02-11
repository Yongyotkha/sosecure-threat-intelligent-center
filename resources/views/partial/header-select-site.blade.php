<header class="dk header b-b" style="display: flex;align-items: center;"> 
    
    <div class="select-box-header">
        <select name="site" id="site_in_siteSetting" class="text-left select2-option form-control select-site"style="min-width:191px">
            {{-- <option value="">All Site</option> --}}
            @if(Modules\SiteSettings\Entities\SiteSettings::select('id','code','name')->where('active',1)->where('deleted_at',null)->get())
                @foreach (Modules\SiteSettings\Entities\SiteSettings::select('id','code','name')->where('active',1)->where('deleted_at',null)->get() as $SiteSettings)
                    @if($SiteSettings->code == @$siteSettings->code)
                        <option value="{{ $SiteSettings->code }}" selected>{{ $SiteSettings->name }}</option>
                    @else
                        <option value="{{ $SiteSettings->code }}">{{ $SiteSettings->name }}</option>
                    @endif
                @endforeach
            @endif

        </select>
    </div>
    <a class="hide-setting menu-site hide-xs-menu btn btn-icon btn-default btn-sm m-r-xs">@icon('solid/bars')</a>
    <a class="btn btn-icon menu-site btn-default btn-sm m-r-xs visible-xs" data-toggle="class:show" data-target="#setting-nav">@icon('solid/bars')</a>
</header>

@push('pagescript')
    <script>

        $("#site_in_siteSetting").change(function() {
            let site_in_siteSetting = $(this).val();
            {{--base_url+'/sitesettings/edit-sitesettings/'--}}
            window.location.href = "{{substr(url()->current(),0,strrpos(url()->current(), "/")+1)}}"+site_in_siteSetting;
        });
    </script>
@endpush