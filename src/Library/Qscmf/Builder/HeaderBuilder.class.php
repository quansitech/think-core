<?php

namespace Qscmf\Builder;

use Bootstrap\RegisterContainer;
use Qscmf\Builder\NavbarRightType\NavbarRightBuilder;
use Qscmf\Builder\NavbarRightType\Num\Num;
use Qscmf\Builder\NavbarRightType\Self\Self_;

class HeaderBuilder
{
    protected $item_list = [];
    protected $header_navbar_right_item_type;

    public function __construct()
    {
        $this->registerNavbarRightLiType();
    }

    protected function registerNavbarRightLiType(){
        static $header_navbar_right_item_type = [];
        if(empty($header_navbar_right_item_type)) {
            $header_navbar_right_item_type = self::registerBaseNavbarRightLiType();
        }

        $this->header_navbar_right_item_type = $header_navbar_right_item_type;
    }

    protected function registerBaseNavbarRightLiType(){
        return [
            'num' => Num::class,
            'self' => Self_::class
        ];
    }

    public function display(){
        $this->item_list = BuilderHelper::checkAuthNode($this->item_list);

        if ($this->item_list){
            foreach ($this->item_list as $option) {
                $tmp = [];
                $tmp['li_attr'] = BuilderHelper::compileHtmlAttr($option['li_attribute']);
                $tmp['li_html'] = (new $this->header_navbar_right_item_type[$option['type']]())->build($option);
                $html = <<<HTML
<li {$tmp['li_attr']}>
{$tmp['li_html']}
</li>
HTML;
                RegisterContainer::registerHeaderNavbarRightHtml($html);
            }
        }
    }

    /**
     *
     * 在header加入一个右侧导航项
     *
     * @param NavbarRightBuilder $navbarRightBuilder
     * @return $this
     */

    public function addNavbarRightItem(NavbarRightBuilder $navbarRightBuilder){
        $this->item_list[] = (array)$navbarRightBuilder;

        return $this;
    }

}