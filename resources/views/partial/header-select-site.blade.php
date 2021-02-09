<header class="dk header b-b" style="display: flex;align-items: center;"> 
    <div class="select-box-header">
        <select name="site" id="site" class="text-left select2-option form-control select-site"style="min-width:191px">
            <option value="" selected="selected">{{ @$siteSettings->name }}</option>
        </select>
    </div>
    <a class="hide-setting menu-site hide-xs-menu btn btn-icon btn-default btn-sm m-r-xs">@icon('solid/bars')</a>
    <a class="btn btn-icon menu-site btn-default btn-sm m-r-xs visible-xs" data-toggle="class:show" data-target="#setting-nav">@icon('solid/bars')</a>
</header>