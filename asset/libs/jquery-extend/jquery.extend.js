// 自己手动扩展的jQuery方法
(function($) {
    $.extend({
        isWindow: function( obj ) {
            return obj != null && obj === obj.window;
        },
    })
})(jQuery);