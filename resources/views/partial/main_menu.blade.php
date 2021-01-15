@php
use App\Menu;
$menu = Menu::where('deleted_at',null)->where('active',1)->orderBy('order','asc')->get();
// dd($menu);
@endphp

{{-- <aside class="bg-{{ get_option('sidebar_theme') }} aside-md b-r {{ settingEnabled('hide_sidebar') ? 'nav-xs' : '' }} hidden-print hidden-xs" id="nav"> --}}
<aside class="bg-{{ get_option('sidebar_theme') }} aside-md b-r {{ settingEnabled('hide_sidebar') ? 'nav-xs' : '' }} hidden-print hidden-xs" id="nav">
    <section class="vbox">
        
        {{-- <header class="header bg-dark text-center clearfix">

            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-{{ get_option('theme_color') }}" title="Language">@icon('solid/lightbulb')</button>
                <div class="btn-group hidden-nav-xs">
                  <button type="button" class="btn btn-sm btn-{{ get_option('theme_color') }} dropdown-toggle" data-toggle="dropdown">
                    @langapp('quick_links')
                    <span class="caret"></span>
                  </button>
                  <ul class="dropdown-menu text-left">
                    <li>
                        @can('users_create')
                        <a href="{{ route('invite') }}" data-toggle="ajaxModal">@icon('solid/envelope-open', 'text-muted') @langapp('send_invite')</a>
                        @endcan
                        @can('projects_create')
                        <a href="{{ route('projects.create') }}">@icon('solid/play', 'text-muted') @langapp('start_project')</a>
                        @endcan
                        @can('contracts_create')
                        <a href="{{ route('contracts.create') }}">@icon('solid/file-contract', 'text-muted') @langapp('start_contract')</a>
                        @endcan

                        @can('tickets_create')
                        <a href="{{ route('tickets.create') }}">@icon('solid/life-ring', 'text-muted') @langapp('new_ticket')</a>
                        @endcan

                    </li>
                    
                  </ul>
                </div>
              </div>

           
        </header> --}}

        <section class="w-f scrollable">
            <div class="slim-scroll" data-color="#333333" data-disable-fade-out="true" data-distance="0" data-height="auto" data-size="5px">
                

                {{-- @php
                    get_menu_html();
                @endphp --}}

                <nav class="nav-primary hidden-xs">
                    {{-- <ul class="nav">

                        @foreach (mainMenu() as $menu)
                    @if (count($menu['children']) > 0)

                        <li class="nav-w-children {{ $page == langapp($menu['name']) && (in_array($menu['module'], array_pluck($menu['children'], 'parent'))) ? 'active'  : '' }}" id="{{ $menu['module'] }}">
                            <a href="{{ site_url($menu['route']) }}">
                                <i class="{{ $menu['icon'] }} icon">
                                    <b class="bg-{{ get_option('theme_color') }}"></b>
                                </i>
                                <span class="pull-right">
                                    <i class="fas fa-angle-down text"></i>
                                    <i class="fas fa-angle-up text-active"></i>
                                </span>
                                <span>
                                    @langapp($menu['name']) 
                                </span>
                            </a>
                            <ul class="nav lt">
                                @foreach ($menu['children'] as $submenu)
                                @if (Auth::user()->can($submenu['module']))
                                   <li class="{{ $page == langapp($submenu['name']) ? 'active' : '' }}">
                                    <a href="{{ site_url($submenu['route']) }}">
                                        <i class="{{ $submenu['icon'] }} icon">
                                            <b class="bg-{{ get_option('theme_color') }}"></b>
                                        </i>
                                        <span>
                                            @langapp($submenu['name']) 
                                        </span>
                                    </a>
                                    </li>
                                @endif
                                
                                @endforeach
                            </ul>
                        </li>
                        @else
                        <li class="{{ $page === langapp($menu['name']) ? 'active' : '' }}">
                            <a href="{{ site_url($menu['route']) }}">
                                <i class="{{ $menu['icon'] }} icon">
                                    <b class="bg-{{ get_option('theme_color') }}"></b>
                                </i>
                                <span>
                                    @langapp($menu['name']) 
                                </span>
                            </a>
                        </li>
                        @endif
                        @endforeach
                    </ul> --}}

                    <ul class="nav">

                        @php
                            // $menu = [];
                            $menu_html = '';
                        // if(isset($_SESSION["menu"])){
                            // unset($_SESSION["lastname"]);
                            // $menu = $_SESSION["menu"];

                            $check_menu_active_arr = '';
                            $check_menu_active_sub_arr = '';
                            if($menu) {
                                foreach($menu as $menu_val) {
                                    $active = '';
                                    $url = '#';
                                    $check_menu_active = '';
                                    $name_val = '';
                                    
                                    if($menu_val->url) {// url
                                        if($menu_val->type_url == 'site_url') {
                                            // dd(TYPE_WEB);
                                            if(TYPE_WEB == 'center') {
                                                $url = site_url($menu_val->url);
                                            } else {
                                                $url = site_url($menu_val->url_client);
                                            }
                                            // $url = site_url($menu_val->url);
                                        } else if ($menu_val->type_url == 'route') {
                                            // $url = route($menu_val->url);
                                            if(TYPE_WEB == 'center') {
                                                $url = route($menu_val->url);
                                            } else {
                                                $url = route($menu_val->url_client);
                                            }
                                        }
                                    }

                                    $check_menu_active_langapp_valval = '';
                                    $check_menu_active_langapp_val_last = '';
                                    if($menu_val->check_menu_active) {// check active
                                        if($menu_val->type_check_menu_active == 'langapp') {
                                            if($menu_val->check_menu_active) {
                                                $check_menu_active_langapp_arr = explode(",",$menu_val->check_menu_active);
                                                if(!empty($check_menu_active_langapp_arr)) {
                                                    foreach($check_menu_active_langapp_arr as $check_menu_active_langapp_val) {
                                                        $check_menu_active_langapp_valval .= langapp($check_menu_active_langapp_val).',';
                                                    }
                                                    // dd($check_menu_active_langapp_arr);
                                                    $check_menu_active_langapp_val_last = rtrim($check_menu_active_langapp_valval,",");
                                                }
                                            }
                                            
                                            // dd($check_menu_active_langapp_val_last);
                                            $check_menu_active = $check_menu_active_langapp_val_last;
                                            if($check_menu_active) {
                                                $check_menu_active_arr = explode(",",$check_menu_active);
                                                // if(count($check_menu_active_arr) > 0) {
                                                //     foreach($check_menu_active_arr as $check_menu_active_arr_val) {
                                                //         $check_menu_active_arr_val
                                                //     }
                                                // }
                                            }

                                        } else if ($menu_val->type_check_menu_active == '') {
                                            if($menu_val->check_menu_active) {
                                                $check_menu_active_arr = explode(",",$menu_val->check_menu_active);
                                            //     if(count($check_menu_active_arr) > 0) {
                                            //         foreach($check_menu_active_arr as $check_menu_active_arr_val) {
                                            //             $check_menu_active_arr_val
                                            //         }
                                            //     }
                                            }
                                            // $check_menu_active = $menu_val->check_menu_active;
                                        }
                                    }

                                    // dd($check_menu_active_arr);

                                    if($check_menu_active_arr) {
                                        foreach($check_menu_active_arr as $check_menu_active_val) {
                                            // dd($check_menu_active_val);
                                            if($page == $check_menu_active_val) {
                                                // dd($check_menu_active_val);
                                                $active = 'active';
                                            }
                                        }
                                    }

                                    

                                    if($menu_val->langapp) {//ชื่อเมนู
                                        $name_val = langapp($menu_val->langapp);
                                    }

                                    if(@$menu_val->get_menu_sub) {


                                        
                                        $menu_sub_html = '';
                                        foreach($menu_val->get_menu_sub as $menu_sub_val) {


                                            $active_sub = '';
                                            $url_sub = '#';
                                            $check_menu_active_sub = '';
                                            $name_val_sub = '';
                                            

                                            if($menu_sub_val->url) {// url
                                                if($menu_sub_val->type_url == 'site_url') {
                                                    $url_sub = site_url($menu_sub_val->url);
                                                    if(TYPE_WEB == 'center') {
                                                        $url = site_url($menu_sub_val->url);
                                                    } else {
                                                        $url = site_url($menu_sub_val->url_client);
                                                    }
                                                } else if ($menu_sub_val->type_url == 'route') {
                                                    // $url_sub = route($menu_sub_val->url);
                                                    if(TYPE_WEB == 'center') {
                                                        $url = route($menu_sub_val->url);
                                                    } else {
                                                        $url = route($menu_sub_val->url_client);
                                                    }
                                                }
                                            }

                                            if($menu_sub_val->check_menu_active) {// check active

                                                $check_menu_active_langapp_arr_sub ='';
                                                $check_menu_active_langapp_valval_sub = '';
                                                if($menu_sub_val->type_check_menu_active == 'langapp') {


                                                    if($menu_val->check_menu_active) {
                                                        $check_menu_active_langapp_arr_sub = explode(",",$menu_sub_val->check_menu_active);
                                                        if(!empty($check_menu_active_langapp_arr_sub)) {
                                                            foreach($check_menu_active_langapp_arr_sub as $check_menu_active_langapp_val_sub) {
                                                                $check_menu_active_langapp_valval_sub .= langapp($check_menu_active_langapp_val_sub).',';
                                                            }
                                                            // dd($check_menu_active_langapp_arr_sub);
                                                            $check_menu_active_langapp_val_last_sub = rtrim($check_menu_active_langapp_valval_sub,",");
                                                        }
                                                    }



                                                    $check_menu_active_sub = $check_menu_active_langapp_val_last_sub;
                                                    if($check_menu_active_sub) {
                                                        $check_menu_active_sub_arr = explode(",",$check_menu_active_sub);
                                                        // if(count($check_menu_active_arr) > 0) {
                                                        //     foreach($check_menu_active_arr as $check_menu_active_arr_val) {
                                                        //         $check_menu_active_arr_val
                                                        //     }
                                                        // }
                                                    }


                                                    

                                                } else if ($menu_sub_val->type_check_menu_active == '') {
                                                    $check_menu_active_sub = $menu_sub_val->check_menu_active;
                                                    if($check_menu_active_sub) {
                                                        $check_menu_active_sub_arr = explode(",",$check_menu_active_sub);
                                                        // if(count($check_menu_active_arr) > 0) {
                                                        //     foreach($check_menu_active_arr as $check_menu_active_arr_val) {
                                                        //         $check_menu_active_arr_val
                                                        //     }
                                                        // }
                                                    }

                                                }
                                            }

                                            if($check_menu_active_sub_arr) {
                                                foreach($check_menu_active_sub_arr as $check_menu_active_sub_val) {
                                                    // dd($check_menu_active_sub_val);
                                                    if($page == $check_menu_active_sub_val) {
                                                        // dd($check_menu_active_sub_val);
                                                        $active_sub = 'active';
                                                    }
                                                }
                                            }




                                            if($menu_sub_val->langapp) {//ชื่อเมนู
                                                $name_val_sub = langapp($menu_sub_val->langapp);
                                            }




                                            $menu_sub_html .= '<li class="'. $active_sub .'">
                                                                    <a href="'. $url_sub .'">
                                                                        <i class="'.$menu_sub_val->icon.'"><b class="bg-info"></b></i>
                                                                        <span>'.$name_val_sub.'</span>
                                                                    </a>
                                                                </li>';
                                        }

                                    }
                                        if($menu_val->is_have_sub == 1) {//ถ้ามี sub menu
                                            $is_have_sub = '<a href="'. $url .'" class="'. @$active_sub .'">
                                                                <i class="'.@$menu_val->icon.'"><b class="bg-info"></b></i>
                                                                <span class="pull-right"><i class="fas fa-angle-down text"></i>
                                                                <i class="fas fa-angle-up text-active"></i></span>
                                                                <span> '.$name_val.' </span>
                                                            </a>
                                                        <ul class="nav lt">'.$menu_sub_html.'</ul>
                                                        ';
                                        } else {
                                            $is_have_sub = '<a href="'. $url .'" class="'. $active .'">
                                                                <i class="'.@$menu_val->icon.'"><b class="bg-info"></b></i>
                                                                    
                                                                <span> '.$name_val.' </span>
                                                            </a>';
                                        }
                                            

                                    
                           

                                    $menu_html .=    '<li class="'. $active .'">
                                                        '.$is_have_sub.'
                                                      </li>';

                                }

                                echo $menu_html;
                            }

                        // }

                        @endphp

                    <!-- Start
                        <li class="{{ $page === langapp('dashboard') ? 'active' : '' }}">
                            <a href="{{ site_url('/dashboardnew') }}">
                                <i class="fas fa-home icon"><b class="bg-info"></b></i>
                                <span> @langapp('dashboard') </span>
                            </a>
                        </li>
                        {{-- <li class="{{ $page === langapp('alert') ? 'active' : '' }}">
                            <a href="{{ site_url('/alert') }}">
                                <i class="fas fa-exclamation-triangle icon"><b class="bg-info"></b></i>
                                <span> @langapp('alert') </span>
                                <span class="count-alert"> 1 </span>
                            </a>
                        </li> --}}

                        <li class="{{ $page === langapp('news') ? 'active' : '' }}">
                            <a href="{{ site_url('/news') }}">
                                <i class="fas fa-newspaper icon"><b class="bg-info"></b></i>
                                <span> @langapp('news') </span>
                                {{-- <span class="count-alert"> 1 </span> --}}
                            </a>
                        </li>

                        {{-- <li class="{{ $page === langapp('manage_assets') ? 'active' : '' }}">
                            <a href="{{ site_url('/manageassets') }}">
                                <i class="fas fa-tasks icon"><b class="bg-info"></b></i>
                                <span> @langapp('manage_assets') </span>
                            </a>
                        </li> --}}
                        
                        <li class="{{ $page === langapp('indicators') ? 'active' : '' }}">
                            <a href="{{ route('indicators.events') }}">
                                <i class="fab fa-searchengin icon"><b class="bg-info"></b></i>
                                <span> @langapp('indicators') </span>
                            </a>
                        </li>

                        <li class="{{ $page === langapp('monitoring_vulnerabilitys') ? 'active' : '' }}">
                            <a href="{{ route('monitoringvulnerabilitys.index') }}">
                                <i class="fas fa-lock icon"><b class="bg-info"></b></i>
                                <span> @langapp('vulnerabilitys') </span>
                            </a>
                        </li>

                        {{-- <li class="nav-w-children {{ $page === langapp('vulnerabilitys') ? 'active' : '' }}">
                            <a href="{{ site_url('/vulnerability') }}" class="{{ $page === langapp('vulnerability') ? 'active' : '' }}">
                                <i class="fas fa-lock icon"><b class="bg-info"></b></i>
                                <span class="pull-right"><i class="fas fa-angle-down text"></i>
                                <i class="fas fa-angle-up text-active"></i></span>
                                <span> @langapp('vulnerabilitys') </span>
                            </a>
                            <ul class="nav lt">
                                <li class="{{ $page === langapp('monitoring_vulnerability') ? 'active' : '' }}">
                                    <a href="{{ route('monitoringvulnerabilitys.index') }}">
                                        <i class="fas fa-angle-right icon"><b class="bg-info"></b></i>
                                        <span> @langapp('monitoring_vulnerability') </span>
                                    </a>
                                </li>
                                <li class="{{ $page === langapp('assets_setting_vulnerabilitys') ? 'active' : '' }}">
                                    <a href="{{ route('assetsettingvulnerabilitys.index') }}">
                                        <i class="fas fa-angle-right icon"><b class="bg-info"></b></i>
                                        <span> @langapp('assets_setting_vulnerability') </span>
                                    </a>
                                </li>
                                <li class="{{ $page === langapp('vulnerability_settings') ? 'active' : '' }}">
                                    <a href="{{ route('vulnerability_settings.index') }}">
                                        <i class="fas fa-angle-right icon"><b class="bg-info"></b></i>
                                        <span> @langapp('settings') </span>
                                    </a>
                                </li>
                            </ul>
                        </li> --}}

                        <li class="nav-w-children {{ $page === langapp('compromised') ? 'active' : '' }}">
                            <a href="{{ site_url('/compromised') }}" class="{{ $page === langapp('compromised') ? 'active' : '' }}">
                                <i class="fas fa-bug icon"><b class="bg-info"></b></i>
                                <span class="pull-right"><i class="fas fa-angle-down text"></i>
                                <i class="fas fa-angle-up text-active"></i></span>
                                <span> @langapp('compromised') </span>
                            </a>
                            <ul class="nav lt">
                                <li class="{{ $page === langapp('monitoring') ? 'active' : '' }}">
                                    <a href="{{ route('monitoringcompromised.index') }}">
                                        <i class="fas fa-angle-right icon"><b class="bg-info"></b></i>
                                        <span> @langapp('monitoring_compromised') </span>
                                    </a>
                                </li>
                                <li class="{{ $page === langapp('assets_setting_compromised') ? 'active' : '' }}">
                                    <a href="{{ route('assetsettingcompromised.index')  }}">
                                        <i class="fas fa-angle-right icon"><b class="bg-info"></b></i>
                                        <span> @langapp('assets_setting_compromised') </span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-w-children {{ $page === langapp('data_leak') ? 'active' : '' }}">
                            <a href="#" class="">
                                <i class="fas fa-database icon"><b class="bg-info"></b></i>
                                <span class="pull-right"><i class="fas fa-angle-down text"></i>
                                <i class="fas fa-angle-up text-active"></i></span>
                                <span> @langapp('data_leak') </span>
                            </a>
                            <ul class="nav lt">
                                <li class="{{ $page === langapp('dark_web') ? 'active' : '' }}">
                                    <a href="{{ site_url('/darkweb') }}">
                                        <i class="fas fa-angle-right icon"><b class="bg-info"></b></i>
                                        <span> @langapp('dark_web') </span>
                                    </a>
                                </li>
                                <li  class="{{ $page === langapp('social') ? 'active' : '' }}">
                                    <a href="{{ site_url('/social') }}">
                                        <i class="fas fa-angle-right icon"><b class="bg-info"></b></i>
                                        <span> @langapp('social') </span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="{{ $page === langapp('webdefacement') ? 'active' : '' }}">
                            <a href="{{ site_url('/webdefacement') }}">
                                <i class="fas fa-globe icon"><b class="bg-info"></b></i>
                                <span> @langapp('webdefacement') </span>
                            </a>
                        </li>
                        {{-- <li class="{{ $page === langapp('manage_customers') ? 'active' : '' }}">
                            <a href="{{ site_url('/clients') }}">
                                <i class="fas fa-user-cog icon"><b class="bg-info"></b></i>
                                <span> @langapp('manage_customers') </span>
                            </a>
                        </li> --}}
                        <li class="{{ $page === langapp('manage_users') ? 'active' : '' }}">
                            <a href="{{ site_url('/users') }}">
                                <i class="fas fa-users icon"><b class="bg-info"></b></i>
                                <span> @langapp('manage_users') </span>
                            </a>
                        </li>
                        {{-- <li class="{{ $page === langapp('settings') ? 'active' : '' }}">
                            <a href="{{ site_url('/settings') }}">
                                <i class="fas fa-cog icon"><b class="bg-info"></b></i>
                                <span> @langapp('settings') </span>
                            </a>
                        </li> --}}

                        <li class="nav-w-children {{ $page === langapp('settings') ? 'active' : '' }}">
                            <a href="#" class="{{ $page === langapp('settings') ? 'active' : '' }}">
                                <i class="fas fa-cog icon"><b class="bg-info"></b></i>
                                <span class="pull-right"><i class="fas fa-angle-down text"></i>
                                <i class="fas fa-angle-up text-active"></i></span>
                                <span> @langapp('settings') </span>
                            </a>
                            <ul class="nav lt">
                                <li class="{{ $page === langapp('category_settings') ? 'active' : '' }}">
                                    <a href="{{ site_url('/categorysettings') }}">
                                        <i class="fas fa-angle-right icon"><b class="bg-info"></b></i>
                                        <span> @langapp('category_settings') </span>
                                    </a>
                                </li>
                                <li class="{{ $page === langapp('site_settings') ? 'active' : '' }}">
                                    <a href="{{ site_url('/sitesettings') }}">
                                        <i class="fas fa-angle-right icon"><b class="bg-info"></b></i>
                                        <span> @langapp('site_settings') </span>
                                    </a>
                                </li>
                                <li class="{{ $page === langapp('scans') ? 'active' : '' }}">
                                    <a href="{{ site_url('/scans') }}">
                                        <i class="fas fa-angle-right icon"><b class="bg-info"></b></i>
                                        <span>@langapp('scans')</span>
                                    </a>
                                </li>

                                <li class="nav-w-children {{ $page === langapp('data_leak') ? 'active' : '' }}">
                                    <a href="#" class="">
                                        <i class="fas fa-angle-right icon"><b class="bg-info"></b></i>
                                        <span class="pull-right"><i class="fas fa-angle-down text"></i>
                                        <i class="fas fa-angle-up text-active"></i></span>
                                        <span> @langapp('data_leak') </span>
                                    </a>
                                    <ul class="nav lt">
                                        <li class="{{ $page === 'Data Feed(Social)' ? 'active' : '' }}">
                                            <a href="{{route('datafeed.index')}}">
                                                <i class="fas fa-angle-right icon"><b class="bg-info"></b></i>
                                                <span>Data Feed(Social)</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="{{ $page === langapp('assets') ? 'active' : '' }}">
                                    <a href="{{ site_url('/assets') }}">
                                        <i class="fas fa-angle-right icon"><b class="bg-info"></b></i>
                                        <span>@langapp('assets')</span>
                                    </a>
                                </li>
                                <li class="{{ $page === langapp('rss_feed') ? 'active' : '' }}">
                                    <a href="{{ site_url('/rssfeedsettings') }}">
                                        <i class="fas fa-angle-right icon"><b class="bg-info"></b></i>
                                        <span> @langapp('rss_feed') </span>
                                    </a>
                                </li>
                                <li class="{{ $page === langapp('keywords') ? 'active' : '' }}">
                                    <a href="{{ site_url('/keywords') }}">
                                        <i class="fas fa-angle-right icon"><b class="bg-info"></b></i>
                                        <span> @langapp('keywords') Setting</span>
                                    </a>
                                </li>

                                <li class="{{ $page === langapp('cpe_setting') ? 'active' : '' }}">
                                    <a href="{{ site_url('/cpesetting') }}">
                                        <i class="fas fa-angle-right icon"><b class="bg-info"></b></i>
                                        <span> @langapp('cpe_setting') </span>
                                    </a>
                                </li>

                                <li class="{{ $page === langapp('function_command') ? 'active' : '' }}">
                                    <a href="{{ site_url('/functioncommandsetting') }}">
                                        <i class="fas fa-angle-right icon"><b class="bg-info"></b></i>
                                        <span> @langapp('function_command') </span>
                                    </a>
                                </li>
                                
                                <li class="{{ $page === langapp('api_indicators') ? 'active' : '' }}">
                                    <a href="{{ site_url('/apiindicators') }}">
                                        <i class="fas fa-angle-right icon"><b class="bg-info"></b></i>
                                        <span> @langapp('api_indicators') </span>
                                    </a>
                                </li>




                                <li class="{{ $page === langapp('vm_client_settings') ? 'active' : '' }}">
                                    <a href="{{ site_url('/vmclientsettings') }}">
                                        <i class="fas fa-angle-right icon"><b class="bg-info"></b></i>
                                        <span> @langapp('vm_client_settings') </span>
                                    </a>
                                </li>
                                <li class="{{ $page === langapp('update_code') ? 'active' : '' }}">
                                    <a href="{{ site_url('/updatecode') }}">
                                        <i class="fas fa-angle-right icon"><b class="bg-info"></b></i>
                                        <span> @langapp('update_code') </span>
                                    </a>
                                </li>
                                <li class="{{ $page === 'settings/general' ? 'active' : '' }}">
                                    <a href="{{ site_url('/settings/general') }}">
                                        <i class="fas fa-angle-right icon"><b class="bg-info"></b></i>
                                        <span> @langapp('general_settings') </span>
                                    </a>
                                </li>
                                <li class="{{ $page === langapp('system_settings') ? 'active' : '' }}">
                                    <a href="{{ site_url('/settings/system') }}">
                                        <i class="fas fa-angle-right icon"><b class="bg-info"></b></i>
                                        <span> @langapp('system_settings') </span>
                                    </a>
                                </li>
                                <li class="{{ $page === langapp('theme_settings') ? 'active' : '' }}">
                                    <a href="{{ site_url('/settings/theme') }}">
                                        <i class="fas fa-angle-right icon"><b class="bg-info"></b></i>
                                        <span> @langapp('theme_settings') </span>
                                    </a>
                                </li>
                                <li class="{{ $page === langapp('system_info') ? 'active' : '' }}">
                                    <a href="{{ site_url('/settings/info') }}">
                                        <i class="fas fa-angle-right icon"><b class="bg-info"></b></i>
                                        <span> @langapp('system_info') </span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                   End Menu -->

                    </ul>
                </nav>

                {{-- <div class="wrapper clearfix small p-10">
                    @foreach (quickAccess() as $key => $entity)
                    <div class="text-center-folded">
                        <span class="hidden-folded">
                            <a class="text-ellipsis" href="{{ $entity['url'] }}">
                                {{ str_limit($entity['name'], 25) }}
                            </a>
                        </span>
                    </div>
                    <div class="progress progress-xxs m-t-xs dk">
                        <div class="progress-bar progress-bar-success" data-placement="top" data-rel="tooltip" style="width: {{ $entity['progress'] }}%;" title="{{ $entity['progress'] }}%">
                        </div>
                    </div>
                    @endforeach
                </div> --}}
            </div>
        </section>
        <footer class="footer lt hidden-xs b-t b-dark website-by" id="changeLanguages">
            <span>
                Powered By <a href="">SOSECURE</a> v1.0.1
                {{-- {{ getCurrentVersion()['version']  }} --}}
            </span>
            {{-- <a class="pull-right btn btn-sm btn-dark btn-icon" data-toggle="class:nav-xs" href="#nav">
                
            </a> --}}
            {{-- @if (settingEnabled('enable_languages'))
            <div class="btn-group dropup pull-right">
                          <button class="btn btn-warning btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                            @icon('solid/globe')
                            </button>
                          <ul class="dropdown-menu">
                            @foreach (languages() as $lang)
                    <li class="">
                        <a href="{{ route('setLanguage', ['lang' => $lang['code']]) }}" title="{{ ucwords(str_replace('_', ' ', $lang['name'])) }}">
                            {{ ucwords(str_replace('_', ' ', $lang['name'])) }}
                        </a>
                    </li>
                    @endforeach
                          </ul>
                        </div>
            @endif --}}
            <div class="btn-group hidden-nav-xs">
            </div>
        </footer>
    </section>
</aside>
