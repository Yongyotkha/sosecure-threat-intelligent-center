<script>
    $(document).ready(function(){
        $('#hide-advance-search').hide();
        $('#advance-search').click(function(){
            $('#hide-advance-search').slideToggle();
        });
    });

    $(function(){
        $('#close_filter').click(function(){
            $('#hide-advance-search').slideToggle();
        });
    });
</script>