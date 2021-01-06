<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Edit Image</title>

    <link rel="stylesheet" href="{{getAsset('css/bootstrap.css')}}">
    <link rel="stylesheet" href="{{getAsset('jquery-ui/jquery-ui.min.css')}}">
    <style>
        body{
            overflow-x: hidden;
            box-sizing: border-box;
        }
        #preview-img-wdfm {
            width: 100%;
            height: 100%;
            border: 1px solid #eee;
            padding: 1rem;
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
      
        .resizable { width: 50px; height: 50px; padding: 0.5em; background: #eee}

    </style>
</head>

<body>
    <div class="header">
        Edit Image
    </div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-3">
                        <h4>Tools</h4>
                    </div>
                    <div class="col-md-9">
                        <div class="draggable" style="position:relative">
                            <div class="ui-widget-content resizable"></div>
                        </div>
                    </div>
                    <hr>
                </div>
            </div>
            <div class="col-md-12">
                <div class="dropBox" style="height: 100%;position:relative">
                    <img src="https://firebasestorage.googleapis.com/v0/b/phish-ai-production.appspot.com/o/LYfzlRVdZPftsYKBQgKf0LkyP3z2%2Fscreenshot%2F92964b45-7858-4725-baf0-f16f5fd1bf89?alt=media&token=63c602fd-a364-4a9c-b89c-4ce09a88ab4a"
                    id="preview-img-wdfm">
                </div>
            </div>
     
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="{{getAsset('jquery-ui/jquery-ui.min.js')}}"></script>
    <script>

        $('.resizable').resizable();

        $(".dropBox").droppable({
            accept: '.draggable',
            drop: function(event, ui) {
                if (ui.draggable.hasClass("draggable")) {
                var $item = $(ui.helper).clone();
                ui.helper.remove();
                leftPosition  = ui.offset.left - $(this).offset().left;
                topPosition   = ui.offset.top - $(this).offset().top;
                console.log("top: " + topPosition + ", left: " + leftPosition); 

                $item.draggable({
                    helper: 'original',
                    cursor: 'move',
                    containment: '.dropBox',
                    tolerance: 'fit',
                });

                $(this).append($item);
                makeDraggable($item);
                }
            }
        });

        function makeDraggable($item) {
            $item.draggable({
                start: function() {},
                stop: function(event, ui) {
                    console.log(ui.position.top);
                    console.log(ui.position.left);
                }
            });
        }

        $(".draggable").draggable({
            containment: ".dropBox",
            appendTo: ".dropBox",
            helper: "clone",
            scroll: true,
            start: function() {},
            stop: function(event, ui) {
                console.log(ui.position.top);
                console.log(ui.position.left);
            }
        });


    </script>
</body>

</html>