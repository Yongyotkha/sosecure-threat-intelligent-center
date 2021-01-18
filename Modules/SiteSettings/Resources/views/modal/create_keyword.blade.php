<div class="modal-dialog modal-dialog-aside">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><i class="fas fa-compress fullscreen-btn" onclick="fullscreen();" datdata-rel="tooltip" title="Fullscreen" data-placement="right"></i> Add Keyword </h4>
        </div>
       
        {!! Form::open(['route' => ['keyword.save',$code], 'class' => 'ajaxifyForm validator', 'novalidate' => '', 'files' => false]) !!}
        <div class="modal-body">
            <div class="form-group row">
                <label class="col-lg-4 control-label">Name <span class="text-danger">*</span> </label>
                <div class="col-lg-8">
                    <input type="text" name="name" class="form-control">
                </div>
            </div>

            <div class="form-group row">
                <label class="col-lg-4 control-label">Type <span class="text-danger">*</span> </label>
                <div class="col-lg-8">
                    <select name="type[]" id="type" class="select2-option form-control "  multiple="multiple" style="min-width: 300px;">
                        <option  value="social">Social</option>
                        <option  value="darkweb">Dark Web</option>
                    </select>
                </div>
                
            </div>

            <div class="form-group row">
                <label class="col-lg-4 control-label">Status </label>
                <div class="col-lg-8">
                    <label class="switch">
                        <input type="checkbox" name="status" checked value="TRUE">
                        <span></span>
                    </label>
                </div>
            </div>
        </div>

        {{-- @include('partial.privacy_consent') --}}
        
        <div class="modal-footer">
            {{-- {!! Form::close() !!} --}}
            {{-- <button type="button" class="btn btn-default btn-rounded" data-dismiss="modal">
                <i class="fas fa-times"></i>
                Close
            </button> --}}
            {!! closeModalButton() !!}
          
            {{-- <button type="submit" class="btn btn-info btn-rounded">
                <i class="fas fa-paper-plane"></i>
                Save
            </button> --}}
            {!! renderAjaxButton() !!}
        </div>
        {!! Form::close() !!}
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
