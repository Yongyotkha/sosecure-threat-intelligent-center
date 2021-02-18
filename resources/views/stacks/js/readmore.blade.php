<script>
        function readmore_btn(table_id,btn_class,togle_class) {
            $(table_id).on('click',btn_class,function(){
                $(this).addClass('awaken');
                if($(this).addClass('awaken')){
                    $(this).prev().toggleClass(togle_class);
                }
            });  
        }
</script>
