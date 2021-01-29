<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header bg-blue">
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            <h4 class="modal-title text-white">@icon('solid/plus') @langapp('create')  </h4>
        </div>


        {!! Form::open(['route' => 'users.perm.save', 'class' => 'bs-example form-horizontal ajaxifyForm']) !!}
        <div class="modal-body">

        

        <div class="form-group">
                <label class="col-lg-3 control-label">@langapp('name') @required</label>
                <div class="col-lg-9">
                    <input type="text" class="form-control" placeholder="post_article" name="name">
                </div>
        </div>

        <div class="form-group">
                <label class="col-lg-3 control-label">@langapp('description') @required</label>
                <div class="col-lg-9">
                    <input type="text" class="form-control" placeholder="Allow user to post articles" name="description">
                </div>
        </div>

        </div>
        
        <div class="modal-footer">

            {!! closeModalButton() !!}
            {!! renderAjaxButton() !!}

            </div>
    {!! Form::close() !!}
</div>

@include('partial.ajaxify')