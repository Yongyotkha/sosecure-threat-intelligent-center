<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header bg-blue">
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            <h4 class="modal-title text-white">{{ @$edit ? ( @$view ? 'View' : 'Edit' ) : 'Add' }}</h4>
        </div>

        <div class="modal-body">
            <form id="form_phishing">

            @if(@$edit)
                <input type="hidden" name="phishing_id" id="phishing_id" value="{{ $query_edit->id }}">
                <input type="hidden" name="phishing_code" id="phishing_code" value="{{ $query_edit->code }}">
            @endif

            <div class="form-group">
                <label for="" class="control-label">Site <span class="text-danger">*</label>
                <select name="site_id" id="site_id" class="text-left select2-option form-control select-site" {{ @$view ? 'disabled' : '' }}>
                    <option value="" disabled {{ @$edit ? '' : 'selected' }}>Select Site</option>
                    @if ($site_settings)

                    @foreach ($site_settings as $site_settings)
                        <option value="{{$site_settings->id}}" {{ @$edit ? ( $site_settings->id == $query_edit->site_id ? 'selected' : '' ) : '' }}>
                            {{$site_settings->name}}
                        </option>
                    @endforeach

                    @endif
                </select>
            </div>
            
            <div class="form-group">
                <label for="" class="control-label">URL Detection</label>
                <input type="text" name="url_detection" id="url_detection" class="form-control" value="{{ @$edit ? $query_edit->url_detection : '' }}" {{ @$view ? 'disabled' : '' }}>
            </div>

            <div class="form-group">
                <label for="" class="control-label">URL</label>
                <input type="text" name="url" id="url" class="form-control" value="{{ @$edit ? $query_edit->url : '' }}" {{ @$view ? 'disabled' : '' }}>
            </div>

            <div class="form-group">
                <label for="" class="control-label">IP Address</label>
                <input type="text" name="ip" id="ip" class="form-control" value="{{ @$edit ? $query_edit->ip : '' }}" {{ @$view ? 'disabled' : '' }}>
            </div>

            <div class="form-group">
                <label for="" class="control-label">Score : <span class="score-text">{{ @$edit ? $query_edit->score : 0 }}</span></label>
                <input type="range" id="score" name="score" class="form-control" list="tickmarks" min="0" max="10" value="{{ @$edit ? $query_edit->score : 0 }}" {{ @$view ? 'disabled' : '' }}>
                <datalist id="tickmarks">
                    <option value="1" label="0"></option>
                    @for($i = 2; $i < 9; $i++)
                    <option value="{{$i}}"></option>
                    @endfor
                    {{-- <option value="2"></option>
                    <option value="3"></option>
                    <option value="4"></option>
                    <option value="5"></option>
                    <option value="6"></option>
                    <option value="7"></option>
                    <option value="8"></option>
                    <option value="9"></option> --}}
                    <option value="10" label="10"></option>
                </datalist>
            </div>

            <div class="form-group">
                <label for="" class="control-label">Type</label>
                <select name="type" id="type" class="text-left select2-option form-control" {{ @$view ? 'disabled' : '' }}>
                    <option value="Referrer" {{ @$edit ? ( $query_edit->type == 'Referrer' ? 'selected' : '' ) : '' }}>Referrer</option>
                    <option value="Threat Feed" {{ @$edit ? ( $query_edit->type == 'Threat Feed' ? 'selected' : '' ) : '' }}>Threat Feed</option>
                    <option value="Domain Name" {{ @$edit ? ( $query_edit->type == 'Domain Name' ? 'selected' : '' ) : '' }}>Domain Name</option>
                    <option value="Other" {{ @$edit ? ( $query_edit->type == 'Other' ? 'selected' : '' ) : '' }}>Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="" class="control-label">Serverity</label>
                <select name="serverity" id="serverity" class="text-left select2-option form-control" {{ @$view ? 'disabled' : '' }}>
                    <option value="Information" {{ @$edit ? ( $query_edit->serverity == 'Information' ? 'selected' : '' ) : '' }}>Information</option>
                    <option value="Low" {{ @$edit ? ( $query_edit->serverity == 'Low' ? 'selected' : '' ) : '' }}>Low</option>
                    <option value="Medium" {{ @$edit ? ( $query_edit->serverity == 'Medium' ? 'selected' : '' ) : '' }}>Medium</option>
                    <option value="High" {{ @$edit ? ( $query_edit->serverity == 'High' ? 'selected' : '' ) : '' }}>High</option>
                    <option value="Critical" {{ @$edit ? ( $query_edit->serverity == 'Critical' ? 'selected' : '' ) : '' }}>Critical</option>
                </select>
            </div>

            <div class="form-group">
                <label class="control-label">Keyword </label>
                <select name="keyword[]" id="keyword" class="select2-option form-control" multiple="multiple">
                    <option value="1">pantip</option>
                    <option value="2">facebook</option>
                    <option value="3">twitter</option>
                </select>
            </div>

            <div class="form-group">
                <label class="control-label">Status</label>
                <div>
                    <label class="switch">
                        <input type="checkbox" name="status" id="status" value="1" {{ @$edit ? ( $query_edit->status == 1 ? 'checked' : '' ) : '' }} {{ @$view ? 'disabled' : '' }}>
                        <span></span>
                    </label>
                </div>
            </div>

            </form>

            @if(@$view && !empty($query_edit->analysis_data))
                @php
                    $analysis = json_decode($query_edit->analysis_data, true);
                @endphp
                @if($analysis)
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="text-uppercase text-blue font-bold" style="margin-top:0"><i class="fas fa-shield-alt"></i> OSINT Analysis Results</h4>
                        
                        <ul class="nav nav-tabs" style="margin-top: 15px;">
                            <li class="active"><a data-toggle="tab" href="#tab-overview" style="color:#333;font-weight:bold;">Overview</a></li>
                            <li><a data-toggle="tab" href="#tab-ssl" style="color:#333;font-weight:bold;">SSL</a></li>
                            <li><a data-toggle="tab" href="#tab-whois" style="color:#333;font-weight:bold;">WHOIS</a></li>
                            <li><a data-toggle="tab" href="#tab-detections" style="color:#d9534f;font-weight:bold;">Detections <span class="badge" style="background-color:#d9534f">{{ $analysis['total_phishing_detections'] ?? 0 }}</span></a></li>
                        </ul>

                        <div class="tab-content" style="padding: 15px; border: 1px solid #ddd; border-top: none; background: #fff;">
                            <div id="tab-overview" class="tab-pane fade in active">
                                <p><strong>URL Analyzed:</strong> <a href="{{ $analysis['url'] ?? '#' }}" target="_blank">{{ $analysis['url'] ?? '-' }}</a></p>
                                <p><strong>Risk Level:</strong> 
                                    @if(($analysis['risk_level'] ?? '') == 'HIGH') <span class="badge" style="background-color: #b93624;">HIGH</span>
                                    @elseif(($analysis['risk_level'] ?? '') == 'MEDIUM') <span class="badge" style="background-color: #fcc838;">MEDIUM</span>
                                    @else <span class="badge" style="background-color: #88ce4f;">LOW / CLEAN</span> @endif
                                </p>
                                <p><strong>Total Scanners:</strong> {{ $analysis['total_engines'] ?? 0 }}</p>
                                
                                @if(!empty($analysis['suspicion_reasons']))
                                    <div class="alert alert-warning" style="margin-top: 10px;">
                                        <strong><i class="fas fa-exclamation-triangle"></i> Suspicious Patterns Found:</strong>
                                        <ul style="margin-top: 5px; margin-bottom: 0;">
                                        @foreach($analysis['suspicion_reasons'] as $reason)
                                            <li>{{ $reason }}</li>
                                        @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>

                            <div id="tab-ssl" class="tab-pane fade">
                                @php $ssl = $analysis['ssl_certificate'] ?? []; @endphp
                                @if(!empty($ssl))
                                    <table class="table table-bordered table-striped" style="margin-bottom:0">
                                        <tbody>
                                            <tr><th width="30%">Has SSL</th><td>{!! $ssl['has_ssl'] ? '<i class="fas fa-check text-success"></i> Yes' : '<i class="fas fa-times text-danger"></i> No' !!}</td></tr>
                                            <tr><th>Is Valid</th><td>{!! $ssl['is_valid'] ? '<i class="fas fa-check text-success"></i> Valid' : '<i class="fas fa-times text-danger"></i> Invalid' !!}</td></tr>
                                            <tr><th>Issuer</th><td>{{ $ssl['issuer'] ?? 'N/A' }}</td></tr>
                                            <tr><th>Expiry Date</th><td>{{ $ssl['expiry_date'] ?? 'N/A' }}</td></tr>
                                            <tr><th>Days Until Expiry</th><td>{{ $ssl['days_until_expiry'] ?? 'N/A' }} days</td></tr>
                                        </tbody>
                                    </table>
                                @else
                                    <p class="text-muted" style="margin:0">No SSL information available.</p>
                                @endif
                            </div>

                            <div id="tab-whois" class="tab-pane fade">
                                @php $whois = $analysis['whois_information'] ?? []; @endphp
                                @if(!empty($whois))
                                    <table class="table table-bordered table-striped" style="margin-bottom:0">
                                        <tbody>
                                            <tr><th width="30%">Registrar</th><td>{{ $whois['registrar'] ?? 'N/A' }}</td></tr>
                                            <tr><th>Creation Date</th><td>{{ $whois['creation_date'] ?? 'N/A' }}</td></tr>
                                            
                                            @if(!empty($whois['whois_warnings']))
                                            <tr><th>Warnings</th>
                                                <td>
                                                    <ul class="text-danger" style="margin-bottom:0; padding-left:15px;">
                                                    @foreach($whois['whois_warnings'] as $warning)
                                                        <li>{{ $warning }}</li>
                                                    @endforeach
                                                    </ul>
                                                </td>
                                            </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                @else
                                    <p class="text-muted" style="margin:0">No WHOIS information available.</p>
                                @endif
                            </div>

                            <div id="tab-detections" class="tab-pane fade">
                                @if(!empty($analysis['phishing_detections']))
                                    <table class="table table-bordered table-hover" style="margin-bottom:0">
                                        <thead>
                                            <tr style="background:#f9f9f9">
                                                <th>Engine</th>
                                                <th>Category</th>
                                                <th>Result</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($analysis['phishing_detections'] as $det)
                                            <tr>
                                                <td><strong>{{ $det['engine'] }}</strong></td>
                                                <td><span class="badge" style="background-color: #b93624;">{{ $det['category'] }}</span></td>
                                                <td>{{ $det['result'] }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p class="text-success" style="margin:0"><i class="fas fa-check-circle"></i> No phishing engines flagged this URL.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            @endif
        </div>
        <div class="modal-footer">
            {!! closeModalButton() !!}
            <button type="button" id="btn_save_phishing" class="btn btn-info formSaving btn-rounded {{ @$view ? 'd-none' : '' }}"><i class="fas fa-paper-plane"></i> Save</button>
        </div>
        
    </div>
</div>
<script>

    $('.sl-2').select2();
    $('#keyword').select2({
        tags: true,
        tokenSeparators: [' ']
    });

    $('#score').change(function(){
        let score = $(this).val();
        $('.score-text').text(score)
    });

    $('.ajaxifyForm_custom').submit(function (event) {
        event.preventDefault();

            $(form_save).html('Processing..<i class="fas fa-spin fa-spinner"></i>');
            $('.formSaving').attr('disabled',true);
            
            var data = new FormData(this);
            if(form_save == '.formSavingAndRun'){
                data.append('formsubmit', 'formSavingAndRun');
            }else if(form_save == '.formPreview'){
                data.append('formsubmit', 'formPreview');
            }else if(form_save == '.formDraft'){
                data.append('formsubmit', 'formDraft');
            }
            axios.post($(this).attr("action"), data)
                .then(function (response) {
                    
                    toastr.success(response.data.message, '@langapp('response_status') ');
                    $(form_save).html('<i class="fas fa-paper-plane"></i>  @langapp('save') </span>');
                    window.location.href = response.data.redirect;
            })
            .catch(function (error) {
                if(error.response.data.exception){
                    $('.formSaving').attr('disabled',false);
                    toastr.error('@langapp('request_failed')' , '@langapp('response_status') ');
                    $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
                }else{
                    $('.formSaving').attr('disabled',false);
                    var errors = error.response.data.errors;
                    var errorsHtml= '';
                    $.each( errors, function( key, value ) {
                        errorsHtml += '<li>' + value[0] + '</li>'; 
                    });
                    toastr.error( errorsHtml , '@langapp('response_status') ');
                    $(form_save).html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
                }
                
                
            }); 
         
    });

    $('#btn_save_phishing').click(function(e){
        e.preventDefault();

        let check_site_val = $('#site_id :selected').val();

        if(check_site_val)
        {
            var formData = new FormData(document.getElementById('form_phishing'));
    
            $.ajax({
                headers:{
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')    
                },
                url: "{{route('phishing_detection.save_phishing')}}",
                type: "post",
                data: formData,
                cache : false,
                contentType: false,
                processData: false, 
                beforeSend: function()
                {
                    $('#btn_save_phishing').html('Processing..<i class="fas fa-spin fa-spinner"></i>');
                    $('.btn').attr('disabled',true);
                },
                success: function(response)
                {   
                    if(response.status == 'success')
                    {
                        toastr.success(response.message);
                        $('.btn').attr('disabled',false);
                        $('#btn_save_phishing').html('<i class="fas fa-paper-plane"></i>  @langapp('save') </span>');
                        $('#ajaxModal').modal('toggle'); 
                        datachart_timeline();
                        datachart_circle();
                        tbl_phishing.ajax.reload();
                    }
                    else
                    {
                        toastr.error(response.message);
                        $('#btn_save_phishing').html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
                        $('.btn').attr('disabled',false);
                    }
                }
            })
        }
        else
        {
            toastr.error('Please select site.');
            $('#btn_save_phishing').html('<i class="fas fa-sync"></i> @langapp('try_again')</span>');
        }

    });

</script>
