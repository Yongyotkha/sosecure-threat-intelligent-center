<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><i class="fas fa-compress fullscreen-btn" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> New User </h4>
        </div>
       
        {!! Form::open(['route' => ['user.save',$code], 'class' => 'ajaxifyForm validator', 'novalidate' => '', 'files' => true]) !!}
        <div class="modal-body">
            {{-- <div class="form-group row">
                <label class="col-lg-4 control-label">Username (e-mail) <span class="text-danger">*</span> </label>
                <div class="col-lg-8">
                    <input type="email" class="form-control" name="username">
                </div>
            </div> --}}
            <div class="form-group row">
                <label class="col-lg-4 control-label">Name <span class="text-danger">*</span> </label>
                <div class="col-lg-8">
                    <input type="text" class="form-control" name="name">
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-4 control-label">Email <span class="text-danger">*</span> </label>
                <div class="col-lg-8">
                    <input type="email" class="form-control" name="email">
                </div>
            </div>
            {{-- <div class="form-group row">
                <label class="col-lg-4 control-label">Set Password <span class="text-danger">*</span> </label>
                <div class="col-lg-8">
                    <input type="password" class="form-control" name="password">
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-4 control-label">Re-enter Password <span class="text-danger">*</span> </label>
                <div class="col-lg-8">
                    <input type="password" class="form-control" name="password_re">
                </div>
            </div> --}}
            <div class="form-group row">
                <label class="col-lg-4 control-label">Role <span class="text-danger">*</span> </label>
                <div class="col-lg-8">
                    <select name="role_id" id="role" class="select2-option form-control">
                        <option value="1">Admin</option>
                        <option value="2">User</option>
                        {{-- <option value="3">Customer</option> --}}
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="col-lg-4 control-label">Status </label>
                <div class="col-lg-8">
                    <label class="switch">
                        <input type="hidden" value="FALSE" name="">
                        <input type="checkbox" name="active" value="TRUE" checked>
                        <span></span>
                    </label>
                </div>
            </div>
        </div>

        {{-- @include('partial.privacy_consent') --}}
        
        <div class="modal-footer">
            {!! Form::close() !!}
            {{-- <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                <i class="fas fa-times"></i>
                Close
            </button> --}}
            {!! closeModalButton() !!}
            {{-- <button type="button" class="btn btn-success btn-rounded">
                <i class="fas fa-play"></i>
                Run Scan And Save Now
            </button> --}}

            {{-- <button type="submit" class="btn btn-info btn-rounded">
                <i class="fas fa-paper-plane"></i>
                Save
            </button> --}}
            {!! renderAjaxButton("Create_User") !!}
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
                    <button type="button" class="close" onclick="close_crop();" aria-label="Close">
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
                    <button type="button" class="btn btn-default btn-rounded" onclick="close_crop();"> <i class="fas fa-times text-muted"></i> Close</button>
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
@include('stacks.js.fullscreen')
@include('partial.ajaxify')

    {{-- Crop Images --}}
    <script>

        $(document).ready(function () {
            $('#role').select2();
        });


        // $(document).ready(function () {
        //     $('#categorys').select2({
        //         placeholder:'Categorys',
        //     });
        // });

        // $(document).ready(function(){
        //    var image = document.getElementById('crop_img');
        //      var input_logo = $('#input_logo');
        //      var cropBoxData;
        //      var canvasData;
        //      var cropper;
        //      var $modal = $('#modal_crop_logo');

        //      input_logo.change(function(event) {
        //          var files = event.target.files;
        //          var done = function(url){
        //              image.src = url;
        //              $modal.modal('show');
        //          };
   
        //          if (files && files.length > 0)
        //          {
        //              reader = new FileReader();
        //              reader.onload = function(event)
        //              {
        //                  done(reader.result);
        //              };
        //              reader.readAsDataURL(files[0]);
        //          }
        //      });
            
        //    $modal.on('shown.bs.modal', function () {
        //        cropper = new Cropper(image, {
        //            dragMode: 'move',
        //            aspectRatio: 16 / 9,
        //            restore: false,
        //            guides: false,
        //            center: false,
        //            highlight: false,
        //            cropBoxMovable: false,
        //            cropBoxResizable: false,
        //            toggleDragModeOnDblclick: false,
        //            preview:'.preview_logo'
        //        });
        //    }).on('hidden.bs.modal', function () {
        //        cropper.destroy();
        //        cropper = null;
        //    });
   
        //    $('#crop').click(function(){
        //        canvas = cropper.getCroppedCanvas({
        //            width: 60,
        //            height: 30,
        //        });
        //        canvas.toBlob(function (blob) {
        //            url = URL.createObjectURL(blob);
        //            var reader = new FileReader();
        //            reader.readAsDataURL(blob);
        //            reader.onloadend = function(){
        //                var base64data = reader.result;
        //                console.log(base64data);
        //                let image_parts = base64data.split(";base64,");
        //                console.log('image_parts : '+image_parts);
        //                let image_type_aux = image_parts[0].split("image/");
        //                console.log('image_type_aux : '+image_type_aux);
        //                let image_type = image_type_aux[1];
        //                console.log('image_type : '+image_type);
        //                console.log('bb : '+image_parts[1]);
        //             //    let image_base64 = base64_decode(image_parts[1]);
        //                let image_base64_de = atob(image_parts[1]);
        //             //    console.log('image_base64 : '+image_base64_de);
        //                $('#logo_preview').attr('src',base64data);
        //                $('#logo_image_base64').val(image_parts[1]);
        //                $('#image_type').val(image_type);
        //             //    $('#input_logo').val(base64data);
        //             //    $.ajax({
        //             //        url:'',
        //             //        method:'GET',
        //             //        data:{image:base64data},
        //             //        success:function(data)
        //             //        {
        //             //            $modal.modal('hide');
        //             //             $('#logo_preview').attr('src',data)
        //             //        }
        //             //    })
        //            };
        //            $modal.modal('hide'); //ตัวนี้สามารถเอาไปใส่ใน Ajax ด้านบนได้เลยนะครับ
        //        });
        //    });
        // });

        // function close_crop(){
        //     var $modal = $('#modal_crop_logo');
        //     $modal.modal('hide');
        // }

    </script>
@endpush

@stack('pagestyle')
@stack('pagescript')
