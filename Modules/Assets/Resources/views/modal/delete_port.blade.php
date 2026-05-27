
@extends('layouts.modal')

@section('title')
    Delete Port
@endsection

@section('content')
    {!! Form::open(['route' => ['assets.methot_delete_port', 'id' => $port->id, 'page' => $menu], 'method' => 'DELETE', 'class' => 'bs-example form-horizontal ajaxifyForm']) !!}
    
    <div class="modal-body">
        <p>Are you sure you want to delete Port <strong>{{ $port->port }}</strong> from <strong>{{ $port->asset_name }}</strong>?</p>
        <input type="hidden" name="id" value="{{ $port->id }}">
    </div>
    <div class="modal-footer">
        {!! closeModal(langapp('close')) !!}
        {!! renderAjaxButton(langapp('delete'), 'fa fa-trash-alt', 'danger') !!}
    </div>
    {!! Form::close() !!}
@endsection
