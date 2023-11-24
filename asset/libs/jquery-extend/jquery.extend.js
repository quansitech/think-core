// 自己手动扩展的jQuery方法
(function($) {
    $.extend({
        isWindow: function( obj ) {
            return obj != null && obj === obj.window;
        },
        isArray: function(obj) {
            return Array.isArray(obj);
        },
        isFunction: function(obj) {
            return typeof obj === "function" && obj instanceof Function;
        },
    })
})(jQuery);