<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header bg-blue">
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            <h4 class="modal-title text-white"><i class="fas fa-compress fullscreen-btn" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> Content Data Leak</h4>
        </div>

        <div class="modal-body">
            <div class="container-fluid">
                <div class="row">
                    <h5>
                        {!!@$feedcontent!!}
                    </h5>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            {!! closeModalButton() !!}
        </div>
    </div>
</div>

@push('pagestyle')
{{-- @include('stacks.css.form')
@include('stacks.css.summernote') --}}
@endpush
@push('pagescript')
{{-- @include('stacks.js.form') --}}
@include('stacks.js.fullscreen')
{{-- @include('scripts.summernote')
@include('stacks.js.markdown') --}}
<script>

</script>
@endpush

@stack('pagestyle')
@stack('pagescript')