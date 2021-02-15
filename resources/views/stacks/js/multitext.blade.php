<script src="{{ getAsset('js/multitext.js') }}"></script>

<script>
    function multi_readmore() {
        $( ".text-trucate-ovf" ).each(function( index ) {
        $(this).multiTextToggleCollapse({
            line: 2
            });
        });
    }
        
</script>