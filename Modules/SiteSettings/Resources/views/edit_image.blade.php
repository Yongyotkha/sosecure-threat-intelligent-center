<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Edit Image</title>

    <link rel="stylesheet" href="{{getAsset('css/bootstrap.css')}}">
    <link rel="stylesheet" href="{{getAsset('plugins/font-awesome/css/font-awesome.min.css')}}">
    <link rel="stylesheet" href="{{getAsset('jquery-ui/jquery-ui.min.css')}}">

    <style>
        body{
            box-sizing: border-box;
        }
        #preview-img-wdfm {
            border: 1px solid #eee;
            box-sizing: border-box
        }

        .header {
            padding: 1rem;
            background: #f2f2f2;
            box-shadow: 0 2px 2px rgba(0, 0, 0, .2);
            margin-bottom: 1rem;
            font-size: 26px;
            font-weight: 700px;
        }
      
        .resizable {   
            width: 50px;
            height: 50px;
            max-height: 100%;
            max-width: 100%; padding: 0.5em; background: #eee
        }

        .resize-auto {
            width: 50px;
            height: 50px;
            max-height: 100%;
            max-width: 100%;
        }
        .draggable {
            background: rgb(241, 206, 8);
            display: inline-block;
            width: 30px;
            height: 30px;
            max-height: 100%;
            max-width: 100%;
        }
        .drag-edit {
            width: 50px;
            height: 50px;
            max-height: 100%;
            max-width: 100%;
            background: #f2f2f2;
            display: inline-block;
        }
        .d-flex {
            display: flex;
        }
        .align-items-center {
            align-items: center;
        }
        .justify-content-center{
            justify-content: center;
        }
        .mr-4{
            margin-right: 2rem;
        }
        .mb-3{
            margin-bottom: 1.5rem;
        }
        .icon-drag{
            position: relative;
        }
        .icon-tool{
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%,-50%);
            cursor: pointer;
        }
        .xicon{
            background: #ff8181;
            color: #fff;
            padding: 1rem;
            width: 20px;
            height: 20px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            top: 0;
            right: 0;
            box-shadow: 0 2px 2px rgba(0, 0, 0, .2);
            text-decoration: none;
        }
        .xicon:hover{
            background: #f75a5a;
            color: #fff;
        }
        
    </style>
</head>

<body>
    <div class="header">
        Edit Image
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex align-items-center mb-3">
                    <div class="mr-4">
                        <h4>Tools</h4>
                    </div>
                    <div class="icon-drag">
                        <div class="draggable" style="position:relative">
                            <span class="icon-tool"><i class="fa fa-crop"></i></span>
                            <span class="rm"></span>
                        </div>
                 
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="dropBox" style="height: 100%;position:relative;display:inline-block">
                    <img src="{{asset($web_defacment_original->image)}}" id="preview-img-wdfm">
                </div>
            </div>
     
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="{{getAsset('jquery-ui/jquery-ui.min.js')}}"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        let webdefacment_data_original_id = '{{ $web_defacment_original -> id }}';
        $(".dropBox").droppable({
            accept: '.draggable',
            drop: function(event, ui) {
                if (ui.draggable.hasClass("draggable")) {
                    var $item = $(ui.helper).clone();
                    ui.helper.remove();

                    leftPosition  = ui.offset.left - $(this).offset().left;
                    topPosition   = ui.offset.top - $(this).offset().top;
                    let item_width = $(ui.draggable).width();
                    let item_height = $(ui.draggable).height();

                    $.ajax({
                        type: 'POST',
                        dataType: "json",
                        url: '{{ route("webdefacement_website.save_item") }}',
                        data: {
                            webdefacment_data_original_id: webdefacment_data_original_id, 
                            top:topPosition, 
                            left:leftPosition,
                            width:item_width, 
                            height:item_height
                        },
                        beforeSend: function() {
                            
                        },
                        success: function(result){
                            $item.attr('data-id', result.data)
                        }
                    }); 


                    $item.draggable({
                        helper: 'original',
                        cursor: 'move',
                        containment: '.dropBox',
                        tolerance: 'fit',
                    });
                    $item.find('.icon-tool').remove();
                    $item.removeClass("draggable");
                    $item.addClass("drag-edit");
                    $item.resizable();
                    $item.appendTo('.dropBox');

                    $item.addClass('remove');
                    var el = `<span><a href='Javascript:void(0)' class="xicon delete" title="Remove">X</a></span>`;
                    $(el).insertAfter($($item.find('.rm')));
                    $item.appendTo('.dropBox');
                    makeDraggable($item);

                    $('.delete').on('click', function () {
                        $(this).parent().parent('span').remove();
                    });
                    $('.delete').on('click', function () {
                        $(this).parent().parent('div').remove();
                    });
                }
            }
        });

        $(".draggable").draggable({
            containment: ".dropBox",
            appendTo: ".dropBox",
            helper: "clone",
            handle: ".icon-tool",
            scroll: true,
            start: function() {},
            stop: function(event, ui) {
                console.log('top :' + ui.position.top);
                console.log('left :' + ui.position.left);
            }
        });

        function makeDraggable($item) {
            $item.resizable({
                stop: function (event, ui) {
                    var width = $(this).width();
                    var height = $(this).height();
                    console.log('width :' + width);
                    console.log('height :' + height);
                }
            });
            $item.draggable({
                accept: '.dropBox',
                start: function() {},
                stop: function(event, ui) {
                    console.log('top :' + ui.position.top);
                    console.log('left :' + ui.position.left);
                }
            });
        }

    </script>
</body>

</html>
