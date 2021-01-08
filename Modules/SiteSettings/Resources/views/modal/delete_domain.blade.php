<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header bg-danger">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">@langapp('delete')   {{  $domain->name  }}</h4>
        </div>

        {!! Form::open(['route' => ['domainsettings.delete_process', $domain->id], 'class' => 'ajaxifyForm', 'method' => 'DELETE']) !!}

        <div class="modal-body">
            <p class="text-danger">@langapp('delete_warning')  </p>

            <input type="hidden" name="checked[]" value="{{  $domain->id  }}">

        </div>
        <div class="modal-footer">

            {!! closeModalButton() !!}
            {!! renderAjaxButton('ok') !!}

        </div>
        
        {!! Form::close() !!}
    </div>
</div>
@include('partial.ajaxify')