<script src="{{ getAsset('js/multitext.js') }}"></script>

<script>
    function multi_readmore() {
        $( "table tbody tr .text-trucate-ovf").each(function() {
        $(this).multiTextToggleCollapse({
            line: 1
            });
        });
    }
        
</script>