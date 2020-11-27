<script>
    $('.show-setting').hide();
    $('.hide-setting').click(function(){
        $('#hide-settings').hide();
        if($('.show-setting').hide()){
            $('.show-setting').show();
        }
    });

    $('.show-setting').click(function(){
        $('#hide-settings').show();
        $(this).hide();
    });
</script>