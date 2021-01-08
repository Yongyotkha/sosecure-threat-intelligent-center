// Crop Logo
    var cropper_logo;
    var imgs_logo = null;
    window.addEventListener('DOMContentLoaded', function () {
        var image = document.getElementById('crop-img-logo');
        var input_logo = $('#file-input');
        var cropBoxData;
        var canvasData;
        var $modal = $('#upload_image_logo_modal');
        var result = document.getElementById('result');

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
                cropper_logo = new Cropper(image, {
                    dragMode: 'move',
                    aspectRatio: 16 / 9,
                    restore: false,
                    guides: false,
                    center: false,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false,
                    preview:'.preview_logo',
                    ready: function () {
                        //Should set crop box data first here
                        cropper_logo.setCropBoxData(cropBoxData).setCanvasData(canvasData);
                    },
                });
            }).on('hidden.bs.modal', function () {
                cropBoxData = cropper_logo.getCropBoxData();
                canvasData = cropper_logo.getCanvasData();
                cropper_logo.destroy();
            });
        });

        $('.upload-image-logo').on('click', function (ev) {
            canvas = cropper_logo.getCroppedCanvas({
                width: 200,
                height: 150,
            }).toDataURL();
            imgs_logo = canvas;
            html = '<img src="' + imgs_logo + '" />';
            $("#preview_cer_img").html(html);
            $("#preview-image_logo").html("");
        });