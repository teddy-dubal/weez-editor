var WeezPdfEngine = (function ($, _, fabric) {
    var $canvas = new fabric.Canvas('container', {
        backgroundColor: 'green'
    });
    var _debug = true;
    /**
     *
     * @returns {undefined}
     */
    var displayEditorBox = function () {
        $('#editbox').show();
        $('.tool').hide();
        $('.' + $('#type').val()).show();
    };
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
            var coord = getRandomLeftTop();
            fabric.Image.fromURL('https://crossorigin.me/http://vignette4.wikia.nocookie.net/simpsons/images/1/1d/Homer_Dog_Tapped_Out.png', function (image) {
                image.set({
                    left: coord.left,
                    top: coord.top,
//                    angle: getRandomInt(-10, 10),
                    crossOrigin: 'anonymous'
                }).scale(getRandomNum(0.5, 0.5))
                        .setCoords();
                $canvas.add(image);
            });
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
        $("#exportImg").click(function () {
            if (!fabric.Canvas.supports('toDataURL')) {
                alert('This browser doesn\'t provide means to serialize canvas to an image');
            } else {
                window.open($canvas.toDataURL('png'));
            }
        });
        $("#exportJson").click(function () {
            console.info(JSON.stringify($canvas));
        });
        $("#duplicateData").click(function () {
//            ajaxObj.data.elt = getElementData();
//            ajaxObj.data.duplicate = true;
//            $.ajax(ajaxObj).done(function (msg) {
//                //alert("Data Saved: ");
//                window.location.reload();
//            });
        });
        $("#deletePersoFile").click(function () {
//            ajaxObj.url = "ajax/delete.php";
//            ajaxObj.data.file = $('#persoFile').val();
//            $.ajax(ajaxObj).done(function (msg) {
//                var data = $.parseJSON(msg);
//                if (!data.status) {
//                    $('.modal-content').html('Impossible de supprimer le modèle par default');
//                    $('.bs-example-modal-sm').modal();
//                } else {
//                    $('.bs-example-modal-sm').on('hidden.bs.modal', function (e) {
//                        window.location.reload();
//                    });
//                    $('.modal-content').html('La page sera rechargé');
//                    $('.bs-example-modal-sm').modal();
//
//                }
//            });
//        });
//        $("#validateEditorboxBtn").click(function () {
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




