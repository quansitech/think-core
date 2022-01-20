<?php


namespace Qscmf\Builder\NavbarRightType;


class NavbarRightBuilder
{

    public $type;
    public $attribute;
    public $auth_node;
    public $options;
    public $li_attribute;

    /**
     * @param $type
     * @return $this
     */
    public function setType($type){
        $this->type = $type;
        return $this;
    }

    /**
     * @param $attribute
     * @return $this
     */
    public  function setAttribute($attribute)
    {
        $this->attribute = $attribute;
        return $this;
    }

    public function setAuthNode($auth_node){
        $this->auth_node = $auth_node;
        return $this;
    }

    public function setOptions($options){
        $this->options = $options;
        return $this;
    }

    public function setLiAttribute($li_attribute){
        $this->li_attribute = $li_attribute;
        return $this;
    }
}