;
(function($) {
    $.fn.popup = function(isShow, opts) {
        var scrollTop = Math.max(parseInt($(document).scrollTop()), Math.abs(parseInt($('html').css('marginTop')))),
            $this = $(this),
            $popupContent = $this.find('.popup-content'),
            $page = $('html,body'),
            $html = $('html'),
            $body = $('body'),
            $popupMask = $('.popup-mask'),
            $window = $(window),
            opt = {},
            keyBroad = false;
            timer = null;

        var defOut = {
            onShowBefore: function() {

            },
            onShowAfter: function() {

            },
            onCloseAfter: function() {

            },
            onCloseBefore: function() {

            }
        };

        // $window.on('resize',function (){
        //     if($window.width() > 720){
        //         return false;
        //     }
        //     keyBroad = !keyBroad;
        //     if(keyBroad){
        //         $('.popup-content.active').css({
        //             transform: 'translate(-50%,-60%)'
        //         });
        //     }else{
        //         $('.popup-content.active').css({
        //             transform: 'translate(-50%,-50%)'
        //         });
        //     }
        // }); 

        opt = $.extend(defOut, opts);

        if (typeof isShow === 'string') {
            if (isShow === 'show') {
                show();
            } else if (isShow === 'hide') {
                hide();
            }
        }

        $('.icon-close-mask').on('click', function() {
            hide();
        });

        function show() {
            opt.onShowBefore();
            $page.addClass('offcanvas').css({
                width: $window.width(),
            });
            $html.css({
                marginTop: -scrollTop + 'px',
                height: $window.height(),
                left: '50%',
                marginLeft: '-' +  ($html.width() / 2) + 'px',
                overflowY: 'hidden'
            });
            $popupMask.removeClass('hidden');
            setTimeout(function() {
                $popupMask.addClass('active');
            }, 1);
            $this.removeClass('hidden').css({
                width: $window.width(),
                height: $window.height()
            });
            setTimeout(function() {
                $popupContent.addClass('active');
            }, 1);
            opt.onShowAfter();
        }

        function hide() {
            if(timer){
                return false;
            }
            timer = setTimeout(function (){

            },300);
            
            if(opt.onCloseBefore() === false){
                return false;
            }

            $popupContent.removeClass('active');
            $popupMask.removeClass('active').one('transitionend', function() {
                $popupMask.addClass('hidden');
                $this.addClass('hidden');
                $page.removeClass('offcanvas');
                $html.css({
                    marginTop: 0,
                    left: '0',
                    marginLeft: 'auto',
                    overflowY: 'auto'
                });
                window.scrollTo(0, scrollTop);
                opt.onCloseAfter();
            });
        }

        return $this;
    }
})(jQuery);