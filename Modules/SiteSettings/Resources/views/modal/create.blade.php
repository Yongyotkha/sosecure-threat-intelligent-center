<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">New Site </h4>
        </div>
        {!! Form::open(['route' => 'sitesettings.api.save', 'class' => 'ajaxifyForm validator', 'novalidate' => '', 'files' => true]) !!}

        <div class="modal-body">
            <div class="form-group row">
                <label class="col-lg-3 control-label">Name <span class="text-danger">*</span> </label>
                <div class="col-lg-9">
                    <div class="">
                        <input type="text" class="form-control" name="name" value="">
                        
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-3 control-label">Description</label>
                <div class="col-lg-9">
                    <div class="">
                        {{-- <input type="text" class="form-control" name="descript" value=""> --}}
                        <textarea id="descript" name="descript" rows="4" class="form-control"></textarea>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-3 control-label">Logo </label>
                <div class="col-lg-9">
                    <div class="">
                        <input id="input_logo" type="file" class="form-control" name="logo" accept="image/*">
                    </div>
                </div>
            </div>
            
            <div class="form-group row">
                <label class="col-lg-3 control-label">Address </label>
                <div class="col-lg-9">
                    <div class="">
                        {{-- <input type="text" class="form-control" name="address" value=""> --}}
                        <textarea id="address" name="address" rows="4" class="form-control"></textarea>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-3 control-label">Remark </label>
                <div class="col-lg-9">
                    <div class="">
                        {{-- <input type="textarea" class="form-control" name="remark" value=""> --}}
                        <textarea id="remark" name="remark" rows="4" class="form-control"></textarea>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-3 control-label">Status </label>
                <div class="col-lg-6">
                    <label class="switch">
                        <input type="hidden" value="FALSE" name="">
                        <input type="checkbox" checked name="active" value="1">
                        <span></span>
                    </label>
                </div>
            </div>
        </div>

        {{-- @include('partial.privacy_consent') --}}
        
        <div class="modal-footer">
            {!! closeModalButton() !!}
            {!! renderAjaxButton() !!}
        </div>
        {!! Form::close() !!}
    </div>
</div>

<!-- Modal Crop Image-->
<div class="modal fade" id="modal_crop_logo" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Cropper</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <h1 class="text-center">Crop Image</h1>
                            <div class="img-container">
                                <img id="crop_img" src="" alt="Picture">
                            </div>
                        </div>
                        <div class="col-md-12 text-center">
                            <h1>Preview Company Logo</h1>
                            <div class="preview_logo"></div>
                        </div>
                    </div>
                </div>
            </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal"> <i class="fas fa-times text-muted"></i> Close</button>
                    <button type="button" class="btn btn-info btn-rounded" id="crop"><i class="fas fa-check"></i> Save</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('pagestyle')
@include('stacks.css.form')
@endpush
@push('pagescript')
@include('stacks.js.form')
@include('partial.ajaxify')

    {{-- Crop Images --}}
    <script>
        $(document).ready(function(){
           var image = document.getElementById('crop_img');
             var input_logo = $('#input_logo');
             var cropBoxData;
             var canvasData;
             var cropper;
             var $modal = $('#modal_crop_logo');
   
             input_logo.change(function(event) {
                 var files = event.target.files;
                 var done = function(url){
                     image.src = url;
                     $modal.modal('show');
                 };
   
                 if (files && files.length > 0)
                 {
                     reader = new FileReader();
                     reader.onload = function(event)
                     {
                         done(reader.result);
                     };
                     reader.readAsDataURL(files[0]);
                 }
             });
            
           $modal.on('shown.bs.modal', function () {
               cropper = new Cropper(image, {
                   dragMode: 'move',
                   aspectRatio: 16 / 9,
                   restore: false,
                   guides: false,
                   center: false,
                   highlight: false,
                   cropBoxMovable: false,
                   cropBoxResizable: false,
                   toggleDragModeOnDblclick: false,
                   preview:'.preview_logo'
               });
           }).on('hidden.bs.modal', function () {
               cropper.destroy();
               cropper = null;
           });
   
           $('#crop').click(function(){
               canvas = cropper.getCroppedCanvas({
                   width: 60,
                   height: 30,
               });
               canvas.toBlob(function (blob) {
                   url = URL.createObjectURL(blob);
                   var reader = new FileReader();
                   reader.readAsDataURL(blob);
                   reader.onloadend = function(){
                       var base64data = reader.result;
                    //    $.ajax({
                    //        url:'',
                    //        method:'POST',
                    //        data:{image:base64data},
                    //        success:function(data)
                    //        {
                    //            $modal.modal('hide');
                    //             $('#show_img').attr('src',data)
                    //        }
                    //    })
                   };
                   $modal.modal('hide'); //ตัวนี้สามารถเอาไปใส่ใน Ajax ด้านบนได้เลยนะครับ
               });
           });
   
        });
    </script>
@endpush

@stack('pagestyle')
@stack('pagescript')