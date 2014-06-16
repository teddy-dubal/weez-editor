
 jQuery(document).ready(function($) {
     // Global property
    var $container = $("#container");
    // Draggable element on board
    function setDraggable(){
        Draggable.create(".elt", {
            bounds: $container,
            zIndexBoost: false,
            type: "x,y",
            throwProps: false,
            onClick: function() {
                amplify.publish( 'click.on.draggable.element', $(this.target) );
            },
            onDragStart: function() {
                amplify.publish( 'drag.on.draggable.element', $(this.target) );
            },
            onDragEnd: function() {
                amplify.publish( 'set.datainfo.on.element', $(this.target) );
                amplify.publish( 'drag.on.draggable.element', $(this.target) );
            }
        });
    }
    
    /**
     * 
     * @param {type} elt
     * @returns {undefined}
     */
    function getEltInfo(elt) {
        var json_data_info = elt.attr('data-info') || '{}';
        var data_info = jQuery.parseJSON(json_data_info);
        $('.form-control').each(function(index, _elt) {
            $(_elt).val(data_info[_elt.id]);
        });
    }
    
    function setEltInfo() {
        var currentId = $('#id').val();
        var json_data_info = $('#'+currentId).attr('data-info') || '{}';
        var data_info = jQuery.parseJSON(json_data_info);
        var mappedAttribute = {};
        $('.form-control').each(function(index, _elt) {
            mappedAttribute[_elt.id] = $(_elt).val();
        });
        var tmp = mmToPixel({'w' : mappedAttribute.w, 'h' : mappedAttribute.h ,'y' : mappedAttribute.y ,'x':mappedAttribute.x});
        tmp.angle = mappedAttribute.angle;
        jQuery.extend( data_info, mappedAttribute );
        $('.elt').each(function(index, _elt) {
            Draggable.get($(_elt)).kill();
        });
        $('#'+currentId).css({width : tmp.w, height : tmp.h, top : tmp.y, left : tmp.x , "-webkit-transform" : "rotate("+tmp.angle+"deg)", "-moz-transform" : "rotate("+tmp.angle+"deg)", "transform" : "rotate("+tmp.angle+"deg)"}).attr('data-info',JSON.stringify(data_info));
        //amplify.publish( 'set.draggable.on.element');
    }
    /**
     * 
     * @param {type} elt
     * @returns {undefined}
     */
    function setDataInfoElement(elt) {
        var gl = elt.position();
        var z = elt.css('z-index');
        var px = gl.left;
        var py = gl.top;
        var json_data_info = elt.attr('data-info') || '{}';
        var data_info = jQuery.parseJSON(json_data_info);
        var tmp = pixelToMm({'x' : px,'y':py});
        data_info.x = tmp.x;
        data_info.y = tmp.y;
        elt.attr('data-info',JSON.stringify(data_info));
    }
    /**
     * 
     * @param {type} elt
     * @returns {undefined}
     */
    function displayEditorBox() {
         $('#editbox').show();
    }
    
    /**
     * 
     * @param {type} values
     * @returns {Number}
     */
    function pixelToMm(values){
        var json_data_info = $('#container').attr('data-info') || '{}';
        var data_info = jQuery.parseJSON(json_data_info);
        jQuery.each(values ,function( index, value ) {
            values[index] = Math.floor(value/data_info['ratio']);
        });
        return values;
    }
    
    /**
     * 
     * @param {type} values
     * @returns {Number}
     */
    function mmToPixel(values){
        var json_data_info = $('#container').attr('data-info') || '{}';
        var data_info = jQuery.parseJSON(json_data_info);
        jQuery.each(values ,function( index, value ) {
            values[index] = value*data_info['ratio'];
        });
        return values;
    }
    
//    function createDraggable() {
//        $("<div class='elt' id='_"+jQuery.now()+"'/>").css({position:"absolute", backgroundColor: 'white', border:"1px solid #454545", width:data_info.w, height:data_info.h, top:data_info.y, left:data_info.x}).attr('data-info',json_data_info).prependTo($container);
//        amplify.publish( 'set.draggable.on.element');
//    }
    
    //Draggable element on toolbox
    Draggable.create("li", {
        onDragEnd: function() {
            if (this.hitTest($container)) {
                var json_data_info = $(this.target).attr('data-info') || '{}';
                var data_info = jQuery.parseJSON(json_data_info);
                if (!jQuery.isEmptyObject(data_info)) {
                    $("<div class='elt' id='_"+jQuery.now()+"'/>").css({position:"absolute", backgroundColor: 'white', border:"1px solid #454545", width:data_info.w, height:data_info.h, top:data_info.y, left:data_info.x}).attr('data-info',json_data_info).prependTo($container);
                    amplify.publish( 'set.draggable.on.element');
                }
            }
            //Return to intial position
            TweenMax.to($(this.target), 0.5, { x : '0px', y : '0px'});
        }
    });

    function initSubscribe(){
        amplify.subscribe( "set.draggable.on.element", function (elt) {setDraggable(elt);});
        amplify.subscribe( "click.on.draggable.element",function (elt){ getEltInfo(elt);});
        amplify.subscribe( "click.on.draggable.element", function () {displayEditorBox();});
        amplify.subscribe( "drag.on.draggable.element",function (elt){ getEltInfo(elt);});
        amplify.subscribe( "drag.on.draggable.element", function () {displayEditorBox();});
        amplify.subscribe( "set.datainfo.on.element", function (elt) {setDataInfoElement(elt);});
        amplify.subscribe( "set.data.from.editor", function () {setEltInfo();});
    }
    /**
     * ACTION BTN
     */
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
    
    $("#validateEditorboxBtn").click(function() {
       amplify.publish( 'set.data.from.editor', $(this.target) );
    });
    
     /**
     * Init drag and drop on element
     */
    setDraggable($container);
    initSubscribe();
});

