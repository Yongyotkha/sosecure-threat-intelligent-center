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
                        <h4>Event Name : Webscanners 2018-02-09 thru current day</h4>
                    </div>

                    <div class="col-md-12">
                        <form action="">
                            <div class="form-group">
                                <label for="">Input Tags :</label>
                                <textarea class="form-control" name="" id="" cols="30" rows="10"></textarea>
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
            <button type="button" class="btn btn-info btn-rounded" onclick="save_assets_manual()">
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
