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
                $('#img-content').find('img').each(function (index, item){
                    var ORIGIN_SRC =  $(item).data('src');
                    if(ORIGIN_SRC){
                       $(item).attr({
                           src: ORIGIN_SRC,
                       });
                    }
                });
            }
            
            function handleImgCss(){
               $('#img-content').find('img').each(function (index, item){
                  $(item)
                      .css({
                            width: 'auto',
                            'max-width': '100%',
                            height: 'auto',
                            filter: 'unset',
                            backgound: 'auto',
                       })
                       .removeClass('img_loading');
                });
            }
            
            function handleCssRule(){
                var CSS_RULE_HTML = $('#img-content').inlineStyler().html();
            }
            
            function filterUselessElem(){
                $('#img-content').children().each(function (index, item){
                    var id = $(item).attr('id');
                    var CONTENTS_ARR = [
                            'activity-name',
                            'meta_content',
                            'js_content',
                        ];
                    var IS_INCLUDE_CONTENT = CONTENTS_ARR.indexOf(id) === -1;
                    if(IS_INCLUDE_CONTENT){
                       $(item).remove();
                    } 
                });
                $('#img-content').find('#js_tags').remove();
                $('#img-content').find('#js_profile_qrcode').remove();
            }
            
            function filterMetaContentWhiteSpace(){
                var META_CONTENT_TRIM_STR = $('#meta_content').get(0).innerHTML.replace(/\s{2}/g, '');
                $('#meta_content').html(META_CONTENT_TRIM_STR);
            }
            
            function resetPublishDate(){
                $('.rich_media_meta_text').css({
                    color: '#999',
                });
            }
            
            function replacePublishTime(){
                var ORIGIN_PUBLISH_TIME_STR = $('#publish_time').text();
                ORIGIN_PUBLISH_TIME_STR = ORIGIN_PUBLISH_TIME_STR.replace('&#22825;', '天');
                ORIGIN_PUBLISH_TIME_STR = ORIGIN_PUBLISH_TIME_STR.replace('&#21608;', '周');
                ORIGIN_PUBLISH_TIME_STR = ORIGIN_PUBLISH_TIME_STR.replace('&#21069;', '前');
                ORIGIN_PUBLISH_TIME_STR = ORIGIN_PUBLISH_TIME_STR.replace('&#26152;', '昨');
                ORIGIN_PUBLISH_TIME_STR = ORIGIN_PUBLISH_TIME_STR.replace('&#20170;', '今');
                ORIGIN_PUBLISH_TIME_STR = ORIGIN_PUBLISH_TIME_STR.replace('&#26376;', '月');
                ORIGIN_PUBLISH_TIME_STR = ORIGIN_PUBLISH_TIME_STR.replace('&#26085;', '日');
                $('#publish_time').text(ORIGIN_PUBLISH_TIME_STR);
            }
            
            $(function (){
                handleImgCss();
                handleImgSrc();
                filterUselessElem();
                filterMetaContentWhiteSpace();
                resetPublishDate();
                replacePublishTime();
                
                parent.window.onChildIFreamLoad(); 
            });
    </script>
EOF;

return $text;