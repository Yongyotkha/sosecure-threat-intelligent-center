<div class="row m-l-none m-r-none m-sm">
    <div class="col-sm-6 col-md-3 padder-v lt border-l pallete b-b">
        <a class="clear" href="{{ route('projects.index', ['filter' => 'Active']) }}">
            <span class="fa-stack fa-2x pull-left m-r-xs">
                <i class="fas fa-square fa-stack-2x text-warning"></i>
                <i class="fas fa-clock fa-stack-1x text-white"></i>
            </span>
            
            <small class="text-uc">@langapp('active')</small>
            <span class="h4 block m-t-xs text-dark">{{ getCalculated('projects_active') }}</span>
        </a>
    </div>

    <div class="col-sm-6 col-md-3 padder-v lt pallete b-b">
        <a class="clear" href="{{ route('reports.index', ['m' => 'projects']) }}">
            <span class="fa-stack fa-2x pull-left m-r-xs">
                <i class="fas fa-square fa-stack-2x text-{{ get_option('theme_color') }}"></i>
                <i class="fas fa-check-circle fa-stack-1x text-white"></i>
            </span>
            <small class="text-uc">@langapp('done')</small>
            <span class="h4 block m-t-xs text-dark">{{ getCalculated('projects_done') }}</span>
        </a>
    </div>

    <div class="col-sm-6 col-md-3 padder-v pallete b-b">
        <a class="clear" href="{{ route('tasks.index') }}">
            <span class="fa-stack fa-2x pull-left m-r-xs">
                <i class="fas fa-square fa-stack-2x text-warning"></i>
                <i class="fas fa-history fa-stack-1x text-white"></i>
            </span>
            
            <small class="text-uc">@langapp('pending') @langapp('tasks')</small>
            <span class="h4 block m-t-xs text-dark">{{ getCalculated('tasks_active') }}</span> </a>
        </div>

    
    
        <div class="col-sm-6 col-md-3 padder-v pallete b-b">
            <a class="clear" href="{{ route('reports.index', ['m' => 'tasks']) }}">
                <span class="fa-stack fa-2x pull-left m-r-xs">
                    <i class="fas fa-square fa-stack-2x text-{{ get_option('theme_color') }}"></i>
                    <i class="fas fa-tasks fa-stack-1x text-white"></i>
                </span>
                
                <small class="text-uc">@langapp('done') @langapp('tasks')</small>
                <span class="h4 block m-t-xs text-dark">{{ getCalculated('tasks_done') }}</span>
            </a>
        </div>
        
    </div>