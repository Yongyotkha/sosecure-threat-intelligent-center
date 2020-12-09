<script>
        $(document).ready(function () {
        $('.ul_submenu').hide();
            $('li.main-link a').click(function(e) {
                $(this).closest("li").find(".ul_submenu").slideToggle();
            });
        });
</script>