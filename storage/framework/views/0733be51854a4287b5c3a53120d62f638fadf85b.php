<aside class="bg-<?php echo e(get_option('sidebar_theme')); ?> aside-md b-r <?php echo e(settingEnabled('hide_sidebar') ? 'nav-xs' : ''); ?> hidden-print hidden-xs" id="nav">
    <section class="vbox">
        
        

        <section class="w-f scrollable">
            <div class="slim-scroll" data-color="#333333" data-disable-fade-out="true" data-distance="0" data-height="auto" data-size="5px">
                
                <nav class="nav-primary hidden-xs">
                    

                    <ul class="nav">
                        <li class="active">
                            <a href="<?php echo e(site_url('/dashboard')); ?>">
                                <i class="fas fa-home icon"><b class="bg-info"></b></i>
                                <span> Dashboard </span>
                            </a>
                        </li>
                        <li>
                            <a href="">
                                <i class="fas fa-exclamation-triangle icon"><b class="bg-info"></b></i>
                                <span> Alert </span>
                                <span class="count-alert"> 1 </span>
                            </a>
                        </li>
                        <li>
                            <a href="">
                                <i class="fas fa-newspaper icon"><b class="bg-info"></b></i>
                                <span> News </span>
                            </a>
                        </li>
                        <li>
                            <a href="">
                                <i class="fab fa-searchengin icon"><b class="bg-info"></b></i>
                                <span> Indicators </span>
                            </a>
                        </li>
                        <li>
                            <a href="">
                                <i class="fas fa-lock icon"><b class="bg-info"></b></i>
                                <span> Volnerability </span>
                            </a>
                        </li>
                        <li>
                            <a href="">
                                <i class="fas fa-bug icon"><b class="bg-info"></b></i>
                                <span> Compromised </span>
                            </a>
                        </li>
                        <li class="nav-w-children" id="menu_sales">
                            <a href="#" class="">
                                <i class="fas fa-database icon"><b class="bg-info"></b></i>
                                <span class="pull-right"><i class="fas fa-angle-down text"></i>
                                <i class="fas fa-angle-up text-active"></i></span>
                                <span> Data Leak </span>
                            </a>
                            <ul class="nav lt" style="display: none;">
                                <li class="">
                                    <a href="">
                                        <i class="fas fa-angle-right icon"><b class="bg-info"></b></i>
                                        <span> Darkweb </span>
                                    </a>
                                </li>
                                <li class="">
                                    <a href="">
                                        <i class="fas fa-angle-right icon"><b class="bg-info"></b></i>
                                        <span> Social </span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="">
                                <i class="fas fa-user icon"><b class="bg-info"></b></i>
                                <span> Users </span>
                            </a>
                        </li>
                        <li>
                            <a href="">
                                <i class="fas fa-cog icon"><b class="bg-info"></b></i>
                                <span> Setting </span>
                            </a>
                        </li>
                    </ul>
                </nav>


                
            </div>
        </section>
        <footer class="footer lt hidden-xs b-t b-dark website-by" id="changeLanguages">
            <span>
                Powered By <a href="">Sosecure</a>
            </span>
            
            
            <div class="btn-group hidden-nav-xs">
            </div>
        </footer>
    </section>
</aside>
