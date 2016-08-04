var WeezPdfEngine = (function ($, Dropzone, fabric) {
    fabric.Object.prototype.toObject = (function (toObject) {
        return function () {
            return fabric.util.object.extend(toObject.call(this), {
                cx: this.cx,
                cy: this.cy,
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
                        left: 0,
                        top: 0,
                        fontFamily: 'helvetica',
                        angle: 0,
                        fill: '#' + getRandomColor(),
                        fontWeight: '',
                        originX: 'left',
                        hasRotatingPoint: true,
                        centerTransform: true
                    });
                    break;
                default:
                    elts = new fabric.IText(text, {
                        left: 0,
                        top: 0,
                        fontFamily: 'helvetica',
                        angle: 0,
                        fill: '#' + getRandomColor(),
                        fontWeight: '',
                        originX: 'left',
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
                    left: 0,
                    top: 0,
                    crossOrigin: 'anonymous'
                }).setCoords();
                image.tag = 'qrcode';
                $canvas.add(image);
            });
        });
        $('#toolbox #barcode').on('click', function (e) {
            fabric.Image.fromURL('/pdf/barcode.png', function (image) {
                image.set({
                    left: 0,
                    top: 0,
                    crossOrigin: 'anonymous'
                }).setCoords();
                image.tag = 'barcode';
                $canvas.add(image);
            });
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
            ajaxObj.url = 'ajax/save.php'
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
            ajaxObj.url = '/_zpl.php'
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
            var eltValue = null;
            var angle = 0, x = 0, y = 0, w = 0, h = 0;
            $.each($('#form').serializeArray(), function (index, _elt) {
                if ('angle' === _elt.name) {
                    angle = parseInt(_elt.value);
                    return true;
                }
                if ($.isNumeric(_elt.value)) {
                    eltValue = parseFloat(_elt.value);
                    if ('mm' === $('#unit').val() && 'fontSize' !== _elt.name) {
                        eltValue = fabric.util.parseUnit(eltValue + 'mm');
                    }
                    activeElement.set(_elt.name, eltValue).setCoords();
                } else {
                    activeElement.set(_elt.name, _elt.value);
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
        $canvas.on("object:added", function (e) {
            var target = e.target;
            var center = null;
            switch (e.target.type) {
                case 'i-text':
                case 'textbox':
                    var itext = target;
                    itext.on('scaling', function () {
                        var data = this.toJSON();
                        center = this.getCenterPoint();
                        this.cx = center.x;
                        this.cy = center.y;
                        _updateForm(data);
                    });
                    itext.on('moving', function () {
                        var data = this.toJSON();
                        center = this.getCenterPoint();
                        this.cx = center.x;
                        this.cy = center.y;
                        _updateForm(data);
                    });
                    itext.on('rotating', function () {
                        var data = this.toJSON();
                        center = this.getCenterPoint();
                        this.cx = center.x;
                        this.cy = center.y;
                        _updateForm(data);
                    });
                    itext.on('editing:exited', function () {
                        var data = this.toJSON();
                        center = this.getCenterPoint();
                        this.cx = center.x;
                        this.cy = center.y;
                        _updateForm(data);
                    });
                    itext.on('selected', function () {
                        $('.all').hide();
                        $('.txt').show();
                        var data = this.toJSON();
                        _updateForm(data);
                    });
                    break;
                case 'image':
                    var image = target;
                    var iw = image.getWidth();
                    var ih = image.getHeight();
                    var rw = $canvas.getWidth() / image.getWidth();
                    var rh = $canvas.getHeight() / image.getHeight();
                    image.on('moving', function () {
                        var data = this.toJSON();
                        center = this.getCenterPoint();
                        this.cx = center.x;
                        this.cy = center.y;
                        _updateForm(data);
                    });
                    image.on('rotating', function () {
                        var data = this.toJSON();
                        center = this.getCenterPoint();
                        this.cx = center.x;
                        this.cy = center.y;
                        _updateForm(data);
                    });
                    image.on('editing:exited', function () {
                        var data = this.toJSON();
                        center = this.getCenterPoint();
                        this.cx = center.x;
                        this.cy = center.y;
                        _updateForm(data);
                    });
                    image.on('selected', function () {
                        $('.all').hide();
                        $('.' + image.tag).show();
                        var data = this.toJSON();
                        _updateForm(data);
                    });
                    if (image.getWidth() > $canvas.getWidth()) {
                        image.setWidth(iw * rw);
                    }
                    if (image.getHeight() > $canvas.getHeight()) {
                        image.setHeight(ih * rh);
                    }
                    break;
            }
        });
        $canvas.on("selection:cleared", function (e) {
            $('.all').hide();
        });
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
                            left: 0,
                            top: 0,
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
        initBase64();
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


