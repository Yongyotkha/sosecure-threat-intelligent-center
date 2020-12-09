
<aside class="bg-<?php echo e(get_option('sidebar_theme')); ?> aside-md b-r <?php echo e(settingEnabled('hide_sidebar') ? 'nav-xs' : ''); ?> hidden-print hidden-xs" id="nav">
    <section class="vbox">
        
        

        <section class="w-f scrollable">
            <div class="slim-scroll" data-color="#333333" data-disable-fade-out="true" data-distance="0" data-height="auto" data-size="5px">
                
                <nav class="nav-primary hidden-xs">
                    

                    <ul class="nav">
                        <li class="<?php echo e($page === langapp('dashboard') ? 'active' : ''); ?>">
                            <a href="<?php echo e(site_url('/dashboardnew')); ?>">
                                <i class="fas fa-home icon"><b class="bg-info"></b></i>
                                <span> <?php echo trans('app.'.'home'); ?> </span>
                            </a>
                        </li>
                        

                        <li class="<?php echo e($page === langapp('news') ? 'active' : ''); ?>">
                            <a href="<?php echo e(site_url('/news')); ?>">
                                <i class="fas fa-newspaper icon"><b class="bg-info"></b></i>
                                <span> <?php echo trans('app.'.'news'); ?> </span>
                                
                            </a>
                        </li>

                        
                        
                        

                        

                        
                        
                        
                        

                        <li class="nav-w-children <?php echo e($page === langapp('settings') ? 'active' : ''); ?>">
                            <a href="#" class="<?php echo e($page === langapp('settings') ? 'active' : ''); ?>">
                                <i class="fas fa-cog icon"><b class="bg-info"></b></i>
                                <span class="pull-right"><i class="fas fa-angle-down text"></i>
                                <i class="fas fa-angle-up text-active"></i></span>
                                <span> <?php echo trans('app.'.'settings'); ?> </span>
                            </a>
                            <ul class="nav lt">

                                <li class="nav-w-children <?php echo e($page === langapp('data_leak') ? 'active' : ''); ?>">
                                    <a href="#" class="">
                                        <i class="fas fa-angle-right icon"><b class="bg-info"></b></i>
                                        <span class="pull-right"><i class="fas fa-angle-down text"></i>
                                        <i class="fas fa-angle-up text-active"></i></span>
                                        <span> <?php echo trans('app.'.'data_leak'); ?> </span>
                                    </a>
                                    <ul class="nav lt">
                                        <li class="<?php echo e($page === 'Data Feed(Social)' ? 'active' : ''); ?>">
                                            <a href="<?php echo e(route('datafeed.index')); ?>">
                                                <i class="fas fa-angle-right icon"><b class="bg-info"></b></i>
                                                <span>Data Feed(Social)</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                                
                            </ul>
                        </li>

                    </ul>
                </nav>

                
            </div>
        </section>
        <footer class="footer lt hidden-xs b-t b-dark website-by" id="changeLanguages">
            <span>
                Powered By <a href="">Sosecure</a> v1.0.1
                
            </span>
            
            
            <div class="btn-group hidden-nav-xs">
            </div>
        </footer>
    </section>
</aside>
