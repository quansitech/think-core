<?php
/**
 * 根据微信url获取富文本
 * User: viki
 * Date: 21-07-22
 * Time: 下午14:33
 */

$text = '';
$url = $_GET['url'];
$url = urldecode($url);
$text = file_get_contents($url);

$cssToInlineStyles = new \TijsVerkoyen\CssToInlineStyles\CssToInlineStyles();
$text=$cssToInlineStyles->convert($text);
$text .= <<<EOF
    <script type="text/javascript" src="../third-party/jquery-1.10.2.min.js"></script>
    <script>
            function handleImgSrc(){
                //因微信公众号文章采用懒加载 这里要取消懒加载
                //处理img
                $('#js_content').find('img').each(function (index, item){
                    var ORIGIN_SRC =  $(item).data('src');
                    if(ORIGIN_SRC){
                       $(item).attr({
                           src: ORIGIN_SRC,
                       });
                    }
                });
            }
            
            function handleImgCss(){
               $('#js_content').find('img').each(function (index, item){
                  $(item)
                      .css({
                            width: 'auto',
                            'max-width': '100%',
                            height: 'auto',
                            filter: 'unset',
                            backgound: 'auto',
                            display: 'unset',
                       })
                       .removeClass('img_loading');
                });
            }
            
            $(function (){
                handleImgCss();
                handleImgSrc();
                
                parent.window.onChildIFreamLoad(); 
            });
    </script>
EOF;

return $text;