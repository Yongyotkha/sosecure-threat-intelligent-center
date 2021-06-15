<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header bg-blue">
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            <h4 class="modal-title text-white"><i class="fas fa-compress fullscreen-btn" onclick="fullscreen();"
                    datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> Insert Tag
            </h4>
        </div>
        <div class="modal-body">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12 m-b-xs">
                        <h4>Event Name : {{ $events[0] -> name }}</h4>
                    </div>
                    <input type="hidden" id="pulse_id" value="{{ $events[0] -> pulse_id }}">
                    <div class="col-md-12">
                        <form action="">
                            <div class="form-group">
                                <label for="">Input Tags :</label>
                                <textarea class="form-control" name="tags" cols="30" rows="10" id="tags_events">{!! $events[0] -> tags !!}</textarea>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                <i class="fas fa-times"></i>
                Close
            </button>
            <button type="button" class="btn btn-info btn-rounded formSaving" onclick="save_assets_manual()">
                <i class="fas fa-paper-plane"></i>
                Save
            </button>
        </div>
    </div>




    @push('pagestyle')
    @include('stacks.css.form')
    @endpush
    @push('pagescript')
    @include('stacks.js.form')
    @include('stacks.js.fullscreen')

    @endpush

    @stack('pagestyle')
    @stack('pagescript')
    <script>
        function save_assets_manual(){
            var form_save = '.formSaving';
            let tags_events = $('#tags_events').val();
            let pulse_id = $('#pulse_id').val();
            $(form_save).html('Processing..<i class="fas fa-spin fa-spinner"></i>');
            $('.formSaving').attr('disabled',true);
            var data = {
                'tags_events': tags_events,
                'pulse_id': pulse_id
            };
            axios.post('{{ route('indicators.save_table_tags') }}', data).then(function (response) {
                toastr.success(response.data.message, '@langapp('response_status') ');
                $(form_save).html('<i class="fas fa-check"></i> @langapp('save') </span>');
                window.location.href = response.data.redirect;
            }).catch(function (error) {
                $('.formSaving').attr('disabled',false);
                if(error.response.data.exception){
                    toastr.error('@langapp('request_failed')' , '@langapp('response_status') ');
                    $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
                }else{
                    var errors = error.response.data.errors;
                    var errorsHtml= '';
                    $.each( errors, function( key, value ) {
                        errorsHtml += '<li>' + value[0] + '</li>'; 
                    });
                    toastr.error( errorsHtml , '@langapp('response_status') ');
                    $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
                }
            }); 
        }
    </script>
