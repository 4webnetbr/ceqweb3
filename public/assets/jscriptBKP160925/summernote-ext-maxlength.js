(function (factory) {
    /* global define */
    if (typeof define === 'function' && define.amd) {
        // AMD. Register as an anonymous module.
        define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports) {
        // Node/CommonJS
        module.exports = factory(require('jquery'));
    } else {
        // Browser globals
        factory(window.jQuery);
    }
}(function ($) {
    // Extends plugins for print plugin.
    $.extend($.summernote.plugins, {
        /**
         * @param {Object} context - context object has status of editor.
         */
        maxlength: function (context) {
            var self = this;
            var layoutInfo = context.layoutInfo;
            var $editor = layoutInfo.editor;
            var $editable = layoutInfo.editable;
            var $statusbar = layoutInfo.statusbar;
            var maxlength = $editor.parent().find('textarea').attr('maxlength');
            if (maxlength) {
                self.$label = null;
                self.initialize = function () {
                    // var label = ui.button({contents:"hi"});
                    var label = document.createElement("span");
                    self.$label = $(label);
                    self.$label.addClass('bootstrap-maxlength badge badge-success');
                    self.$label.css({position: 'absolute', left:'50%', transform:'translateX(-50%)'});
                    $statusbar.append(self.$label);
                    self.toggle($editable.html().replace(/(<([^>]+)>)/ig,"").length);
                    $editable.on('keyup', function(){
                        var length = $editable.html().replace(/(<([^>]+)>)/ig,"").length;
                        self.toggle(length);
                    });
                };
                self.toggle = function(length){
                    self.$label.text(length +" / "+ maxlength);
                    if(length >= maxlength){
                        self.$label.addClass('badge-danger');
                        self.$label.removeClass('badge-success');
                    }else{
                        self.$label.addClass('badge-success');
                        self.$label.removeClass('badge-danger');
                    }
                };
            }
        }
    });
}));