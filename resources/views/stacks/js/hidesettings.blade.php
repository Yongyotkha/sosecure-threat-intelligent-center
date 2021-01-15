<script>
    
    $('.show-setting').show();
    $('#hide-settings').hide();
    $('.hide-setting').click(function(){
        $('#hide-settings').hide();
        if($('.show-setting').hide()){
            $('.show-setting').show();
        }
    });

    $('.show-setting').click(function(){
        $(this).hide();
        $('#hide-settings').show();
    });
</script>