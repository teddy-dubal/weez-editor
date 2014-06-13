
 jQuery(document).ready(function($) {
    var $container = $("#container");
    // Draggable element on board
    function setDraggable($container){
        Draggable.create(".elt", {
            bounds: $container,
            zIndexBoost: false,
            type: "x,y",
            throwProps: false,
            onClick: function() {
                getEltInfo($(this.target));
            },
            onDragStart: function() {
                getEltInfo($(this.target));
            },
            onDragEnd: function() {
                var elt = $(this.target);
                var gl = elt.position();
                var z = elt.css('z-index');
                var px = gl.left;
                var py = gl.top;
                var json_data_info = elt.attr('data-info') || '{}';
                var data_info = jQuery.parseJSON(json_data_info);
                data_info.x = Math.floor(px);
                data_info.y = Math.floor(py);
                data_info.z = z;
                elt.attr('data-info',JSON.stringify(data_info));
            },
            onUpdate: function() {

            },
        });
    }
    
    function getEltInfo(elt) {
        var json_data_info = elt.attr('data-info') || '{}';
        var data_info = jQuery.parseJSON(json_data_info);
        $('.form-control').each(function(index, _elt) {
            $(_elt).val(data_info[_elt.id]);
        });
        
    }
    //Draggable element on toolbox
    $toolbox = $("#toolbox");
    Draggable.create("li", {
        //bounds: $container,
        //zIndexBoost: false,
        type: "x,y",
        throwProps: false,
        onClick: function() {
            console.info($(this.target).position().left,this.x);
        },
        onDragEnd: function() {
            if (this.hitTest($container)) {
                var json_data_info = $(this.target).attr('data-info') || '{}';
                var data_info = jQuery.parseJSON(json_data_info);
                if (!jQuery.isEmptyObject(data_info)) {
                    $("<div class='elt' id='_"+jQuery.now()+"'/>").css({position:"absolute", backgroundColor: 'white', border:"1px solid #454545", width:data_info.w, height:data_info.h, top:data_info.y, left:data_info.x}).attr('data-info',json_data_info).prependTo($container);
                    setDraggable($container);	
                }
            }
            //Return to intial position
            TweenMax.to($(this.target), 0.5, { x : '0px', y : '0px'});
        }
    });

    $("#saveData").click(function() {
        event.preventDefault();
        var _data = {};
        $container.children().each(function(index, elt) {
            var json_data_info = $(elt).attr('data-info') || '{}';
            var data_info = jQuery.parseJSON(json_data_info);
            _data[elt.id] = data_info;
        });
        $.ajax({
            type: "POST",
            url: "save.php",
            data: {'elt': _data, 'container': {'w': $container.width(), 'h': $container.height()}, 'format': 'a4'}
        }).done(function(msg) {
            //alert("Data Saved: ");
        });
    });
    setDraggable($container);
});

