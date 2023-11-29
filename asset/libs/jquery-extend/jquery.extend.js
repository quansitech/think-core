// 自己手动扩展的jQuery方法
(function($) {
    $.fn.size = function() {
        return this.length;
    }
})(jQuery);