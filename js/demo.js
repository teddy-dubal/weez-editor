/*
 var WeezPdfEngine = (function($){
 var MY_CONSTANT = 123;
 var _myPrivateVariable = 'TEST MEH';
 var _$myPrivateJqueryObject = $('div.content');

 var _myPrivateMethod = function(){
 alert('I am private!');
 };

 var myPublicMethod = function(){
 console.log('Public much?');
 }

 return {
 myPublicMethod : myPublicMethod
 };

 })(jQuery);
 */

var WeezPdfEngine = (function($, Draggable, Amplify, TweenMax, _) {
    var $container = $("#container");
    var _debug = true;
    /**
     *
     * @returns {undefined}
     */
    var setBoardElementsDraggable = function() {
        Draggable.create(".elt", {
            bounds: $container,
            zIndexBoost: false,
            type: "x,y",
            throwProps: false,
            onClick: function() {
                Amplify.publish('click.on.draggable.element', $(this.target));
            },
            onDragStart: function() {
                Amplify.publish('drag.on.draggable.element', $(this.target));
            },
            onDragEnd: function() {
                Amplify.publish('update.element.position', $(this.target));
                Amplify.publish('drag.on.draggable.element', $(this.target));
            }
        });
    };
    /**
     *
     * @param {type} elt
     * @returns {undefined}
     */
    var fillFormWithElementData = function(elt) {
        var json_data_info = elt.attr('data-info') || '{}';
        var data_info = $.parseJSON(json_data_info);
        $('#editbox .form-control').each(function(index, _elt) {
            $(_elt).val(_o(_elt.id, data_info));
        });
        !_debug || console.info('fillFormWithElementData', data_info);
    };
    /**
     *
     * @returns {undefined}
     */
    var updateElementFromFormData = function() {
        var elt = $('#' + $('#id').val());
        var json_data_info = elt.attr('data-info') || '{}';
        var data_info = $.parseJSON(json_data_info);
        var mappedAttribute = {};
        $('#editbox .form-control').each(function(index, _elt) {
            //console.info();
            _o(_elt.id, mappedAttribute, $(_elt).val())
            //mappedAttribute[_elt.id] = $(_elt).val();
        });
        var tmp = mmToPixel({'w': mappedAttribute.w, 'h': mappedAttribute.h, 'y': mappedAttribute.y, 'x': mappedAttribute.x});
        tmp.angle = mappedAttribute.angle;
        tmp.z = mappedAttribute.z;
        $.extend(data_info, mappedAttribute);
        elt.css({width: tmp.w, height: tmp.h, top: tmp.y, left: tmp.x, 'z-index': tmp.z}).attr('data-info', JSON.stringify(data_info));
        TweenMax.to(elt, 0.5, {rotation: tmp.angle});
        !_debug || console.info('updateElementFromFormData', mappedAttribute);
    };
    /**
     *
     * @param {type} elt
     * @returns {undefined}
     */
    var updateElementCoordinate = function(elt) {
        var gl = elt.position();
        var px = gl.left;
        var py = gl.top;
        var tmp = pixelToMm({'x': px, 'y': py});
        var json_data_info = elt.attr('data-info') || '{}';
        var data_info = $.parseJSON(json_data_info);
        data_info.x = tmp.x;
        data_info.y = tmp.y;
        elt.attr('data-info', JSON.stringify(data_info));
        !_debug || console.info('updateElementPosition', px, py);
    };
    /**
     *
     * @returns {undefined}
     */
    var displayEditorBox = function() {
        $('#editbox').show();
        $('.tool').hide();
        $('.' + $('#type').val()).show();
    };

    var getElementData = function() {
        var _data = {};
        $container.children().each(function(index, elt) {
            var json_data_info = $(elt).attr('data-info') || '{}';
            var data_info = $.parseJSON(json_data_info);
            _data[elt.id] = data_info;
        });
        return _data;
    }
    /**
     *
     * @param {type} values
     * @returns {Number}
     */
    var pixelToMm = function(values) {
        var json_data_info = $('#container').attr('data-info') || '{}';
        var data_info = $.parseJSON(json_data_info);
        $.each(values, function(index, value) {
            values[index] = Math.floor(value / data_info['ratio']);
        });
        return values;
    };
    /**
     *
     * @param {type} values
     * @returns {Number}
     */
    var mmToPixel = function(values) {
        var json_data_info = $('#container').attr('data-info') || '{}';
        var data_info = $.parseJSON(json_data_info);
        $.each(values, function(index, value) {
            values[index] = value * data_info['ratio'];
        });
        return values;
    };
    /**
     *
     * @param {String} key
     * @param {Object} obj
     * @returns {mixed}
     */
    var _oo = function(key, obj, value) {
        var ks = key.split('.'),
                ksl = ks.length;
        var res = _(ks).reduce(function(m, n) {
            if (typeof value !== 'undefined') {
                console.info(ksl, m, n);
                return m[n] = {};
            }
            return m[n] || '';
        }, obj);
        return res;
    };
    /**
     *
     * @param {string} key
     * @param {object} obj
     * @param {mixed} value
     * @returns {@var;value|String}
     */
    var _o = function(key, obj, value) {
        if (typeof key === 'string')
            return _o(key.split('.'), obj, value);
        else if (key.length === 1 && typeof value !== 'undefined')
            return obj[key[0]] = value;
        else if (key.length === 0)
            return obj || '';
        else {
            if (typeof value !== 'undefined' && typeof obj[key[0]] === 'undefined')
                obj[key[0]] = {};
            return _o(key.slice(1), obj[key[0]], value);
        }


    };

    /**
     *
     * @returns {Number}
     */
    var getMaxIndexes = function() {
        var ind = 0;
        $('#container >').each(function(index, value) {
            var tmpInd = parseInt($(value).css('z-index'));
            ind = (ind > tmpInd) ? ind : tmpInd;
        });
        return ind;
    }
    /**
     *
     * @returns {undefined}
     */
    var initSubscribe = function() {
        Amplify.subscribe("set.draggable.on.element", function() {
            setBoardElementsDraggable();
        });
        Amplify.subscribe("click.on.draggable.element", function(elt) {
            fillFormWithElementData(elt);
        });
        Amplify.subscribe("click.on.draggable.element", function() {
            displayEditorBox();
        });
        Amplify.subscribe("drag.on.draggable.element", function(elt) {
            fillFormWithElementData(elt);
        });
        Amplify.subscribe("drag.on.draggable.element", function() {
            displayEditorBox();
        });
        Amplify.subscribe("set.data.from.editor", function() {
            updateElementFromFormData();
        });
        Amplify.subscribe("update.element.position", function(elt) {
            updateElementCoordinate(elt);
        });
    };
    /**
     *
     * @returns {undefined}
     */
    var initDraggable = function() {
        setBoardElementsDraggable();
    }
    /**
     *
     * @returns {undefined}
     */
    var initToolbox = function() {
        Draggable.create("li", {
            onDragEnd: function() {
                if (this.hitTest($container)) {
                    var json_data_info = $(this.target).attr('data-info') || '{}';
                    var data_info = $.parseJSON(json_data_info);
                    data_info.id = '_' + $.now();
                    data_info.z = getMaxIndexes() + 1;
                    if (!$.isEmptyObject(data_info)) {
                        $("<div class='elt' id='" + data_info.id + "'/>").css({position: "absolute", backgroundColor: '#2ECCFA', border: "1px solid #454545", width: data_info.w, height: data_info.h, top: data_info.y, left: data_info.x, 'z-index': data_info.z}).attr('data-info', json_data_info).html(data_info.default).prependTo($container);
                        Amplify.publish('set.draggable.on.element');
                    }
                }
//Return to intial position
                TweenMax.to($(this.target), 0.5, {x: '0px', y: '0px'});
            }
        });
    };
    /**
     *
     * @returns {undefined}
     */
    var initBtn = function() {
        var ajaxObj = {
            type: "POST",
            url: "ajax/save.php",
            data: {'container': {'w': $container.width(), 'h': $container.height()}, 'format': 'a4'}
        };

        $("#saveData").click(function() {
            ajaxObj.data.elt = getElementData();
            ajaxObj.data.file = $('#persoFile').val();
            $.ajax(ajaxObj).done(function(msg) {
                //alert("Data Saved: ");
            });
        });
        $("#duplicateData").click(function() {
            ajaxObj.data.elt = getElementData();
            ajaxObj.data.duplicate = true;
            $.ajax(ajaxObj).done(function(msg) {
                //alert("Data Saved: ");
                window.location.reload();
            });
        });
        $("#deletePersoFile").click(function() {
            ajaxObj.url = "ajax/delete.php";
            ajaxObj.data.file = $('#persoFile').val();
            $.ajax(ajaxObj).done(function(msg) {
                var data = $.parseJSON(msg);
                if (!data.status) {
                    $('.modal-content').html('Impossible de supprimer le modèle par default');
                    $('.bs-example-modal-sm').modal();
                } else {
                    $('.bs-example-modal-sm').on('hidden.bs.modal', function(e) {
                        window.location.reload();
                    });
                    $('.modal-content').html('La page sera rechargé');
                    $('.bs-example-modal-sm').modal();

                }
            });
        });
        $("#validateEditorboxBtn").click(function() {
            Amplify.publish('set.data.from.editor', $(this.target));
        });
    };
    /**
     *
     * @returns {undefined}
     */
    var init = function() {
        initToolbox();
        initBtn();
        initSubscribe();
        initDraggable();
    };
    return {
        init: init
    };
})(jQuery, Draggable, amplify, TweenMax, _);
/**
 *
 * @param {type} param
 */
jQuery(document).ready(function() {
    WeezPdfEngine.init();
});




