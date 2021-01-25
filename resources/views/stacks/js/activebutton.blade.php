<script>
    function active_btn(name_button){
        $(name_button).on('click',function(){
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
        });
    }
</script>