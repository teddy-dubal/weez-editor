var WeezPdfEngine = (function ($, _, fabric) {
    var $canvas = new fabric.Canvas('container');
    var _debug = true;
    /**
     *
     * @param {type} elt
     * @returns {undefined}
     */
    var fillFormWithElementData = function (elt) {
        var json_data_info = elt.attr('data-info') || '{}';
        var data_info = $.parseJSON(json_data_info);
        $('#editbox .form-control').each(function (index, _elt) {
            $(_elt).val(_o(_elt.id, data_info));
        });
        !_debug || console.info('fillFormWithElementData', data_info);
    };
    /**
     *
     * @returns {undefined}
     */
    var updateElementFromFormData = function () {
        var elt = $('#' + $('#id').val());
        var json_data_info = elt.attr('data-info') || '{}';
        var data_info = $.parseJSON(json_data_info);
        var mappedAttribute = {};
        $('#editbox .form-control').each(function (index, _elt) {
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
    var updateElementCoordinate = function (elt) {
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
    var displayEditorBox = function () {
        $('#editbox').show();
        $('.tool').hide();
        $('.' + $('#type').val()).show();
    };

    var getElementData = function () {
        var _data = {};
        $container.children().each(function (index, elt) {
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
    var pixelToMm = function (values) {
        var json_data_info = $('#container').attr('data-info') || '{}';
        var data_info = $.parseJSON(json_data_info);
        $.each(values, function (index, value) {
            values[index] = Math.floor(value / data_info['ratio']);
        });
        return values;
    };
    /**
     *
     * @param {type} values
     * @returns {Number}
     */
    var mmToPixel = function (values) {
        var json_data_info = $('#container').attr('data-info') || '{}';
        var data_info = $.parseJSON(json_data_info);
        $.each(values, function (index, value) {
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
    var _oo = function (key, obj, value) {
        var ks = key.split('.'),
                ksl = ks.length;
        var res = _(ks).reduce(function (m, n) {
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
    var _o = function (key, obj, value) {
        if (typeof key === 'string')
            return _o(key.split('.'), obj, value);
        else if (key.length === 1 && typeof value !== 'undefined')
            return obj[key[0]] = value;
        else if (key.length === 0)
            return obj || '';
        else {
            if (typeof value !== 'undefined' && typeof obj[key[0]] === 'undefined')
                obj[key[0]] = {};
            return _o(key.slice(1), obj[key[0]] || '', value);
        }
    };

    /**
     *
     * @returns {Number}
     */
    var getMaxIndexes = function () {
        var ind = 0;
        $('#container >').each(function (index, value) {
            var tmpInd = parseInt($(value).css('z-index'));
            ind = (ind > tmpInd) ? ind : tmpInd;
        });
        return ind;
    }
    /**
     *
     * @returns {undefined}
     */
    var initSubscribe = function () {

    };
    /**
     *
     * @returns {undefined}
     */
    var initDraggable = function () {
    };

    /**
     *
     * @returns {undefined}
     */
    var initToolbox = function () {
        $('#toolbox #text').on('click', function (e) {
            var width = $canvas.getWidth();
            var height = $canvas.getHeight();

            var text = 'Lorem ipsum\nLorem ipsum';
            var textSample = new fabric.IText(text, {
                left: width / 2,
                top: height / 2,
                fontFamily: 'helvetica',
                angle: 0,
                fill: '#' + getRandomColor(),
                fontWeight: '',
                originX: 'left',
                hasRotatingPoint: true,
                centerTransform: true
            });

            $canvas.add(textSample);
        });
        $('#toolbox #img').on('click', function (e) {
            console.info('img');
        });
        $('#toolbox #qrcode').on('click', function (e) {
            console.info('qrcode');
        });
        $('#toolbox #barcode').on('click', function (e) {
            console.info('barcode');
        });
    };
    /**
     *
     * @returns {undefined}
     */
    var initBtn = function () {
        var ajaxObj = {
            type: "POST",
            url: "ajax/save.php",
            data: {container: {w: $canvas.getWidth(), h: $canvas.getHeight()}, format: 'a4'}
        };

        $("#saveData").click(function () {
            ajaxObj.data.elt = getElementData();
            ajaxObj.data.file = $('#persoFile').val();
            $.ajax(ajaxObj).done(function (msg) {
                //alert("Data Saved: ");
            });
        });
        $("#duplicateData").click(function () {
            ajaxObj.data.elt = getElementData();
            ajaxObj.data.duplicate = true;
            $.ajax(ajaxObj).done(function (msg) {
                //alert("Data Saved: ");
                window.location.reload();
            });
        });
        $("#deletePersoFile").click(function () {
            ajaxObj.url = "ajax/delete.php";
            ajaxObj.data.file = $('#persoFile').val();
            $.ajax(ajaxObj).done(function (msg) {
                var data = $.parseJSON(msg);
                if (!data.status) {
                    $('.modal-content').html('Impossible de supprimer le modèle par default');
                    $('.bs-example-modal-sm').modal();
                } else {
                    $('.bs-example-modal-sm').on('hidden.bs.modal', function (e) {
                        window.location.reload();
                    });
                    $('.modal-content').html('La page sera rechargé');
                    $('.bs-example-modal-sm').modal();

                }
            });
        });
        $("#validateEditorboxBtn").click(function () {
            Amplify.publish('set.data.from.editor', $(this.target));
        });
    };
    var initCanvas = function () {
        $canvas.on("object:moving", function (e) {
            var obj = e.target;
            var halfw = obj.getWidth() / 2;
            var halfh = obj.getHeight() / 2;
            var bounds = {
                tl: {x: halfw, y: halfh},
                br: {x: obj.canvas.getWidth(), y: obj.canvas.getHeight()}
            };
            // top-left  corner
            if (obj.top < bounds.tl.y || obj.left < bounds.tl.x) {
                obj.top = Math.max(obj.top, 0);
                obj.left = Math.max(obj.left, 0);
            }
            // bot-right corner
            if (obj.top + obj.getHeight() > bounds.br.y || obj.left + obj.getWidth() > bounds.br.x) {
                obj.top = Math.min(obj.top, $canvas.getHeight() - obj.getHeight());
                obj.left = Math.min(obj.left, $canvas.getWidth() - obj.getWidth());
            }
        });
    };
    /**
     *
     * @returns {undefined}
     */
    var init = function () {
        initCanvas();
        initToolbox();
        initBtn();
        initSubscribe();
        initDraggable();
    };
    return {
        init: init
    };
})(jQuery, _, fabric);
/**
 *
 * @param {type} param
 */
jQuery(document).ready(function () {
    WeezPdfEngine.init();
});




