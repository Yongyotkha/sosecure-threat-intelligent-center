<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header bg-blue">
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            <h4 class="modal-title text-white"><i class="fas fa-compress fullscreen-btn" onclick="fullscreen();"
                    datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> Mapping
                    {{-- Insert Actor --}}
            </h4>
        </div>
        <div class="modal-body">
            <div class="container-fluid">
                <div class="row">
                    @if(empty($events) || !isset($events[0]))
                    <div class="col-md-12">
                        <div class="alert alert-warning">
                            ไม่พบ Event สำหรับ Mapping (pulse_id ไม่ถูกต้องหรือไม่มีในระบบ)
                        </div>
                    </div>
                    @else
                    <div class="col-md-12 m-b-xs">
                        <h4>Event Name : {{ $events[0]->name }}</h4>
                    </div>
                    <input type="hidden" id="pulse_id" value="{{ $events[0]->pulse_id }}">
                    <div class="col-md-12">
                        <form action="">
                            <div class="form-group">
                                <label for="">Input Tags :</label>
                                <textarea class="form-control" name="tags" cols="30" rows="3" id="tags_events">{!! $events[0]->tags !!}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="">Actor :</label>
                                <select name="category_actor[]" id="category_actor" class="select2-option form-control" multiple>
                                    @if($actors != null)
                                        @foreach ($actors as $data_actor)
                                            <option value="{{ is_object($data_actor) ? $data_actor->adversary_uuid : ($data_actor['adversary_uuid'] ?? '') }}" selected>{{ is_object($data_actor) ? $data_actor->adversary_name : ($data_actor['adversary_name'] ?? '') }}</option>
                                        @endforeach
                                    @endif
                                </select>                            
                            </div>
                            <div class="form-group">
                                <label for="">Campaign :</label>
                                {{-- <input type="text" name="category_campaign" id="category_campaign" class="form-control"> --}}
                                <select name="category_campaign[]" id="category_campaign" class="select2-option form-control" multiple>
                                    @if($campainge != null)
                                        @foreach($campainge as $data_camp)
                                            <option value="{{ is_object($data_camp) ? $data_camp->adversary_uuid : ($data_camp['adversary_uuid'] ?? $data_camp) }}" selected>{{ is_object($data_camp) ? $data_camp->adversary_name : ($data_camp['adversary_name'] ?? $data_camp) }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            {{-- <div class="form-group">
                                <label for="">Techniques :</label>
                                <select name="category_techniques[]" id="category_techniques" class="select2-option form-control" multiple>
                                    @if($techniques != null)
                                        @foreach ($techniques as $data_techniques)
                                            <option value="{{$data_techniques->id}}">
                                                {{$data_techniques->tactics_id}}
                                                {{$data_techniques->tactics_name}}::
                                                {{$data_techniques->code}}
                                                {{$data_techniques->name}}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>                            
                            </div> --}}
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                <i class="fas fa-times"></i>
                Close
            </button>
            @if(!empty($events) && isset($events[0]))
            <button type="button" class="btn btn-info btn-rounded formSaving" onclick="save_assets_manual()">
                <i class="fas fa-paper-plane"></i>
                Save
            </button>
            @endif
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
            let category_actor = $('#category_actor').val();
            let category_campaign = $('#category_campaign').val();
            let category_techniques = $('#category_techniques').val();
            $(form_save).html('Processing..<i class="fas fa-spin fa-spinner"></i>');
            $('.formSaving').attr('disabled',true);
            var data = {
                'tags_events': tags_events,
                'pulse_id': pulse_id,
                'category_actor': category_actor,
                'category_campaign': category_campaign,
                'category_techniques': category_techniques
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

        $('#category_actor').select2({
            tag: true,
            tokenSeparators: [' '],
            placeholder: 'select actor',
            minimumInputLength: 1,
            ajax: {
                url: "{!! route('rssfeedsettings.rss_select_actor_news_create') !!}",
                dataType: 'json',
                method: 'post',
                delay: 250,
                processResults: function(data){
                    return {
                        results: $.map(data, function(item){
                            return {
                                text: item.name,
                                id: item.adversary_uuid
                            }
                        })
                    };
                },
                cache: true
            }
        });

        $('#category_campaign').select2({
            tag: true,
            tokenSeparators: [' '],
            placeholder: 'select campainge',
            minimumInputLength: 1,
            multiple: true,
            ajax: {
                url: "{!! route('rssfeedsettings.new_select_campainge') !!}",
                dataType: 'json',
                method: 'post',
                deley: 250,
                processResults: function(data){
                    return {
                        results: $.map(data, function(item){
                            return {
                                text: item.name,
                                id: item.campainge_uuid
                            }
                        })
                    };
                },
                cache: true
            }
        });

        {{-- $('#category_techniques').select2({
            tag: true,
            tokenSeparators: [' '],
            placeholder: 'select techniques',
            minimumInputLength: 1,
            ajax: {
                url: "{!! route('indicators.select_techniques') !!}",
                dataType: 'json',
                method: 'post',
                delay: 250,
                processResults: function(data){
                    return {
                        results: $.map(data, function(item){
                            return {
                                text: item.name,
                                id: item.name
                            }
                        })
                    };
                },
                cache: true
            }
        }); --}}

    </script>
