var WeezGearEngine = (function ($, _, fabric) {
    fabric.Object.prototype.toObject = (function (toObject) {
        return function () {
            return fabric.util.object.extend(toObject.call(this), {
//                id: this.id,
                tag: this.tag
            });
        };
    })(fabric.Object.prototype.toObject);

    var $canvas = new fabric.Canvas('container', {
//        backgroundColor: 'green',
        height: fabric.util.parseUnit('297mm'),
        width: fabric.util.parseUnit('210mm'),
    });
    var _debug = true;

    /**
     *
     * @returns {undefined}
     */
    var initCanvas = function () {
        var url = '/data/perso/' + json_file;
        $.getJSON(url, function (data) {
            $canvas.loadFromJSON(data, function () {
                $canvas.renderAll();
            });
//            $canvas.toDataURL('png');
        });
    };
    /**
     *
     * @returns {undefined}
     */
    var init = function () {
        initCanvas();
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
    WeezGearEngine.init();
});




