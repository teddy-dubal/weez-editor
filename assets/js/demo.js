var WeezPdfEngine = (function ($, Dropzone, fabric) {
    fabric.Object.prototype.toObject = (function (toObject) {
        return function () {
            return fabric.util.object.extend(toObject.call(this), {
                bx: this.bx,
                by: this.by,
                name: this.name,
                tag: this.tag,
                locked: this.locked || false
            });
        };
    })(fabric.Object.prototype.toObject);
    var $canvas = new fabric.Canvas('container', {
        backgroundColor: 'white',
    });
    var _debug = true;
    var _updateForm = function (_data) {
        var targetFormInput = $('#form').find('input,select,textarea');
        var unit = $('#unit').val();
        $.each(targetFormInput, function (index, _elt) {
            var res = _elt.id;
            if (typeof _data[res] != "undefined") {
                try {
                    if ($.isNumeric(_data[res]) && res != 'angle' && unit == 'mm') {
                        if (res == 'fontSize') {
                            return false;
                        }
                        $(_elt).val(pixel2mm(_data[res]));
                    } else {
                        $(_elt).val(_data[res]);
                    }
                } catch (e) {
                    console.info(e);
                }
            }
        });
    };

    var $importedFontsString = $('#fontList').attr("href").slice(40).replace(/\+/g, " ").replace(/:400,400i,700,700i/g, "");
    var $importedFontsArray  = ($importedFontsString.length == 0) ? [] : $importedFontsString.split("|");
    console.info ($importedFontsArray);
    $.each($importedFontsArray, function (index, fontName) {
        $('#fontFamily').append('<option value="' + fontName + '">' + fontName + '</option>');
    });

    /**
     *
     * @returns {undefined}
     */
    var initToolbox = function () {
        $('#toolbox #textElts').on('click', function (e) {
            var selectedOption = $('#tag option:selected');
            var type = selectedOption.data('type') || 'i-text';
            var text = selectedOption.text();
            var elts = null;
            switch (type) {
                case 'textbox':
                    elts = new fabric.Textbox(text, {
                        left: $canvas.getWidth() / 2,
                        top: $canvas.getHeight() / 2,
                        fontFamily: 'helvetica',
                        angle: 0,
                        fill: '#' + getRandomColor(),
                        fontWeight: '',
                        originX: 'center',
                        originY: 'center',
                        hasRotatingPoint: true,
                        centerTransform: true
                    });
                    break;
                default:
                    elts = new fabric.IText(text, {
                        left: $canvas.getWidth() / 2,
                        top: $canvas.getHeight() / 2,
                        fontFamily: 'helvetica',
                        angle: 0,
                        fill: '#' + getRandomColor(),
                        fontWeight: '',
                        originX: 'center',
                        originY: 'center',
                        hasRotatingPoint: true,
                        centerTransform: true
                    });
                    break;
            }
            elts.tag = selectedOption.val();
            $canvas.add(elts);
        });
        $('#toolbox #image').on('click', function (e) {
            $('.image').show();
        });
        $('#toolbox #qrcode').on('click', function (e) {
            fabric.Image.fromURL('/pdf/qrcode.png', function (image) {
                image.set({
                    left: image.getWidth() / 2,
                    top: image.getHeight() / 2,
                    originX: 'center',
                    originY: 'center',
                    crossOrigin: 'anonymous'
                }).setCoords();
                image.tag = 'qrcode';
                $canvas.add(image);
            });
        });
        $('#toolbox #barcode').on('click', function (e) {
            fabric.Image.fromURL('/pdf/barcode.png', function (image) {
                image.set({
                    left: image.getWidth() / 2,
                    top: image.getHeight() / 2,
                    originX: 'center',
                    originY: 'center',
                    crossOrigin: 'anonymous'
                }).setCoords();
                image.tag = 'barcode';
                $canvas.add(image);
            });
        });
        $('#textDecorationBox #italic').on('click', function (e) {
            var activeElement = $canvas.getActiveObject();
            if (activeElement.fontStyle == 'italic'){
                activeElement.fontStyle = '';
            } else {
                activeElement.fontStyle = 'italic';
            }
            $canvas.renderAll();
        });
        $('#textDecorationBox #bold').on('click', function (e) {
            var activeElement = $canvas.getActiveObject();
            if (activeElement.fontWeight == 'bold'){
                activeElement.fontWeight = '';
            } else {
                activeElement.fontWeight = 'bold';
            }
            $canvas.renderAll();
        });
        $('#textDecorationBox #overline, #textDecorationBox #lineThrough, #textDecorationBox #underline').on('click', function (e) {
           var activeElement = $canvas.getActiveObject();
           var $value = $(this).val();
           if (activeElement.textDecoration.search($value) == -1){
               activeElement.textDecoration = activeElement.textDecoration + $value;
           } else {
               activeElement.textDecoration = activeElement.textDecoration.replace($value, '');
           }
            $canvas.renderAll();
        });
        $('#fontFamily').on('change', function(e){
            var activeElement = $canvas.getActiveObject();
            activeElement.fontFamily = $(this).val();
            $canvas.renderAll();
        });
        $('#fontSize').on('change', function(e){
            var activeElement = $canvas.getActiveObject();
            activeElement.fontSize = $(this).val();
            $canvas.renderAll();
        });
        $('#lineHeight').on('change', function(e){
            var activeElement = $canvas.getActiveObject();
            activeElement.lineHeight = $(this).val();
            $canvas.renderAll();
        });
    };
    /**
     *
     * @returns {undefined}
     */
    var initBtn = function () {
        var ajaxObj = {
            type: "POST",
            data: {}
        };
        $("#save").click(function () {
            $('.all').hide();
            $canvas.deactivateAll().renderAll();
            ajaxObj.url = 'ajax/save.php';
            ajaxObj.data.json = JSON.stringify($canvas);
            ajaxObj.data.format = JSON.stringify($canvas.format);
            ajaxObj.data.file = $('#persoFile').val();
            $.ajax(ajaxObj).done(function (msg) {
                //alert("Data Saved: ");
            });
        });
        $("#duplicate").click(function () {
            $('.all').hide();
            $canvas.deactivateAll().renderAll();
            ajaxObj.url = 'ajax/save.php';
            ajaxObj.data.json = JSON.stringify($canvas);
            ajaxObj.data.file = $('#persoFile').val();
            ajaxObj.data.format = JSON.stringify($canvas.format);
            ajaxObj.data.duplicate = true;
            $.ajax(ajaxObj).done(function (msg) {
                window.location.reload();
            });
        });
        $("#importJson").click(function () {
            var ts = (new Date()).getTime();
            $('.all').hide();
            var url = '/data/perso/' + $('#persoFile').val() + '?t=' + ts;
            $.getJSON(url, function (data) {
                $canvas.loadFromJSON(data, function () {
                    $canvas.format = {format: $canvas.format};
                    $canvas.renderAll();
                });
            });
        });
        $("#exportImg").click(function () {
            $('.all').hide();
            $canvas.deactivateAll().renderAll();
            if (!fabric.Canvas.supports('toDataURL')) {
                alert('This browser doesn\'t provide means to serialize canvas to an image');
            } else {
                window.open($canvas.toDataURL('png'));
            }
        });
        $("#exportJson").click(function () {
            $('.all').hide();
            $('#exportJsonBox').text(JSON.stringify($canvas));
        });
        $("#exportZpl").click(function () {
            $('.all').hide();
            ajaxObj.url = '/_zpl.php';
            ajaxObj.data.json = JSON.stringify($canvas);
            ajaxObj.data.file = $('#persoFile').val();
            ajaxObj.data.format = $('#format').val();
            $.ajax(ajaxObj).done(function (msg) {
                msg = JSON.parse(msg);
                $('#exportJsonBox').html(msg.zpl);
            });
        });
        $("#exportPdf").click(function () {
            $('.all').hide();
            window.open($(this).data('url') + $('#persoFile').val());
        });
        $("#validateEditorboxBtn").click(function () {
            var activeElement = $canvas.getActiveObject();
            var eltValue = null, eltName = null;
            var angle = 0, x = 0, y = 0, w = activeElement.getWidth(), h = activeElement.getHeight();
            $.each($('#form').serializeArray(), function (index, _elt) {
                eltName = _elt.name;
                if ('angle' === eltName) {
                    angle = parseInt(_elt.value);
                    return true;
                }
                if ($.isNumeric(_elt.value)) {
                    eltValue = parseFloat(_elt.value);
                    if ('mm' === $('#unit').val() && 'fontSize' !== eltName) {
                        eltValue = fabric.util.parseUnit(eltValue + 'mm');
                    }
                    if ('bx' === eltName) {
                        eltName = 'left';
                        eltValue += w / 2;
                    }
                    if ('by' === eltName) {
                        eltName = 'top';
                        eltValue += h / 2;
                    }
                    activeElement.set(eltName, eltValue).setCoords();
                } else {
                    activeElement.set(eltName, _elt.value);
                }
            });
            activeElement.setAngle(angle).setCoords();
            $canvas.renderAll();
        });
        $("#deleteEditorboxBtn").click(function () {
            var activeElement = $canvas.getActiveObject();
            $canvas.remove(activeElement);
            $('.all').hide();
            var rawElement = $('.dropzone-previews').get(0);
            var myDropzone = rawElement.dropzone;
            if (activeElement.tag == "image") {
                $.each(myDropzone.files, function (i, elem) {
                    if (elem.newName == activeElement.name) {
                        myDropzone.removeFile(elem);
                    }
                });
            }
        });
        $("#send-backwards").click(function () {
            var activeObject = $canvas.getActiveObject();
            if (activeObject) {
                $canvas.sendBackwards(activeObject);
            }
        });
        $("#send-to-back").click(function () {
            var activeObject = $canvas.getActiveObject();
            if (activeObject) {
                $canvas.sendToBack(activeObject);
            }
        });
        $("#bring-forward").click(function () {
            var activeObject = $canvas.getActiveObject();
            if (activeObject) {
                $canvas.bringForward(activeObject);
            }
        });
        $("#bring-to-front").click(function () {
            var activeObject = $canvas.getActiveObject();
            if (activeObject) {
                $canvas.bringToFront(activeObject);
            }
        });
        $("#persoFile").change(function () {
//            window.location.href = '/?file=' + $("#persoFile").val();
        });
        $("#format").change(function () {
            window.location.href = '/?format=' + $("#format").val();
        });
        $("#unit").change(function () {
            $('.all').hide();
            $canvas.deactivateAll().renderAll();
        });
    };
    /**
     *
     * @returns {undefined}
     */
    var initCanvas = function () {
        var selectedOption = $('#format option:selected');
        var dimentions = {
            width: fabric.util.parseUnit(selectedOption.data('width') + 'mm'),
            height: fabric.util.parseUnit(selectedOption.data('height') + 'mm')
        };
        $canvas.setDimensions(dimentions);
        var obj = {
            format: {
                name: $('#format').val(),
                dimension: {px: {width: $canvas.getWidth(), height: $canvas.getHeight()}, mm: {width: selectedOption.data('width'), height: selectedOption.data('height')}}
            }
        };
        $canvas.format = obj;
        initCanvasAdd();
        $canvas.on("selection:cleared", function (e) {
            $('.all').hide();
        });
        $canvas.on('after:render', function () {
            $canvas.contextContainer.strokeStyle = '#555';
            $canvas.forEachObject(function (obj) {
                var bound = obj.getBoundingRect();
                $canvas.contextContainer.strokeRect(
                        bound.left + 0.5,
                        bound.top + 0.5,
                        bound.width,
                        bound.height
                        );
            });
        });
        $canvas.on("object:moving", function (e) {
            var obj = e.target;
            var bounding = obj.getBoundingRect();
            var halfw = bounding.width / 2;
            var halfh = bounding.height / 2;
            var bounds = {
                tl: {x: halfw, y: halfh},
                br: {x: obj.canvas.getWidth(), y: obj.canvas.getHeight()}
            };
//            // top-left  corner
            if (obj.top < bounds.tl.y || obj.left < bounds.tl.x) {
                obj.top = Math.max(obj.top, halfh);
                obj.left = Math.max(obj.left, halfw);
            }
            // bot-right corner
            if (obj.top + halfh > bounds.br.y || obj.left + halfw > bounds.br.x) {
                obj.top = Math.min(obj.top, $canvas.getHeight() - halfh);
                obj.left = Math.min(obj.left, $canvas.getWidth() - halfw);
            }
        });
    };
    /**
     *
     * @returns {undefined}
     */
    var initCanvasAdd = function () {
        $canvas.on("object:added", function (e) {
            var target = e.target;
            //Binding for bounding rect of object
            var setCoords = target.setCoords.bind(target);
            target.on({
                moving: setCoords,
                scaling: setCoords,
                rotating: setCoords
            });
            var _update_form = function () {
                var data = this.toJSON();
                var bound = this.getBoundingRect();
                data.bx = this.bx = bound.left;
                data.by = this.by = bound.top;
                if ('image' === this.type) {
                    var iw = this.getWidth();
                    var ih = this.getHeight();
                    var rw = $canvas.getWidth() / this.getWidth();
                    var rh = $canvas.getHeight() / this.getHeight();
                    if (this.getWidth() > $canvas.getWidth()) {
                        this.setWidth(iw * rw);
                    }
                    if (this.getHeight() > $canvas.getHeight()) {
                        this.setHeight(ih * rh);
                    }
                }
                $('.all').hide();
                $('.' + this.tag).show();
                if ('i-text' === this.type || 'textbox' === this.type) {
                    $('.txt').show();
                }
                _updateForm(data);
            };
            target.on({
                moving: _update_form,
                scaling: _update_form,
                rotating: _update_form,
                selected: _update_form,
                'editing:exited': _update_form
            });
        });
    };
    var initImageHandler = function () {
        _debug || console.log('initImageHandler');
        var $dpz = $('.dropzone-previews');
        if (!$dpz.length) {
            return false;
        }
        Dropzone.autoDiscover = false;
        var dpz = new Dropzone(".dropzone-previews", {
            url: '/ajax/upload.php',
            paramName: "files", // The name that will be used to transfer the file
            maxFilesize: 2, // MB
            uploadMultiple: true,
            parallelUploads: 1,
            acceptedFiles: 'image/*',
            clickable: '.dropzone-previews',
            init: function () {
                this.on("processing", function (file) {
                    file.newName = file.name + file.size + new Date().getTime();
                });
                this.on("success", function (file, data) {
                    var obj = JSON.parse(data);
                    fabric.Image.fromURL(obj.file, function (image) {
                        image.set({
                            left: image.getWidth() / 2,
                            top: image.getHeight() / 2,
                            originX: 'center',
                            originY: 'center',
                            crossOrigin: 'anonymous'
                        }).setCoords();
                        image.tag = 'image';
                        image.name = file.newName;
                        $canvas.add(image);
                    });
                });
                this.on("error", function (file, errorMessage) {
                    this.removeAllFiles();
                });
            }
        });
    };
    /**
     *
     * @returns {undefined}
     */
    var initBase64 = function () {
        if ($('#base64')) {
            $canvas.loadFromJSON($('#base64').data('udata'), function () {
                $canvas.renderAll();
                $('#base64').text($canvas.toDataURL('png'));
            });
        }
    };
    /**
     *
     * @returns {undefined}
     */
    var init = function () {
        initCanvas();
        initToolbox();
        initImageHandler();
        initBtn();
//        initBase64();
    };
    return {
        init: init
    };
})(jQuery, Dropzone, fabric);
/**
 *
 * @param {type} param
 */
jQuery(document).ready(function () {
    WeezPdfEngine.init();
});


