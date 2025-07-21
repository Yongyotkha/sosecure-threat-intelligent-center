<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header bg-warning">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Make a new list again   {{  $domain->name  }}</h4>
        </div> 

        {!! Form::open(['route' => ['domainsettings.redo_process', $domain->code], 'class' => 'ajaxifyForm', 'method' => 'PUT']) !!}

        <div class="modal-body">
            <p class="text-warning"> Make a new list again</p>

            <input type="hidden" name="checked[]" value="{{  $domain->code  }}">

        </div>
        <div class="modal-footer">

            {!! closeModalButton() !!}
            {!! renderAjaxButton('ok') !!}

        </div>
        
        {!! Form::close() !!}
    </div>
</div>
@include('partial.ajaxify')
