<?php
namespace Qscmf\Builder\FormType;

use Bootstrap\RegisterContainer;
use Qscmf\Builder\FormType\Address\Address;
use Qscmf\Builder\FormType\Arr\Arr;
use Qscmf\Builder\FormType\Board\Board;
use Qscmf\Builder\FormType\Checkbox\Checkbox;
use Qscmf\Builder\FormType\City\City;
use Qscmf\Builder\FormType\Citys\Citys;
use Qscmf\Builder\FormType\Date\Date;
use Qscmf\Builder\FormType\Datetime\Datetime;
use Qscmf\Builder\FormType\District\District;
use Qscmf\Builder\FormType\Districts\Districts;
use Qscmf\Builder\FormType\Editormd\Editormd;
use Qscmf\Builder\FormType\File\File;
use Qscmf\Builder\FormType\Files\Files;
use Qscmf\Builder\FormType\Hidden\Hidden;
use Qscmf\Builder\FormType\Icon\Icon;
use Qscmf\Builder\FormType\Key\Key;
use Qscmf\Builder\FormType\Num\Num;
use Qscmf\Builder\FormType\Password\Password;
use Qscmf\Builder\FormType\Picture\Picture;
use Qscmf\Builder\FormType\PictureIntercept\PictureIntercept;
use Qscmf\Builder\FormType\Pictures\Pictures;
use Qscmf\Builder\FormType\PicturesIntercept\PicturesIntercept;
use Qscmf\Builder\FormType\Province\Province;
use Qscmf\Builder\FormType\Radio\Radio;
use Qscmf\Builder\FormType\Select\Select;
use Qscmf\Builder\FormType\Select2\Select2;
use Qscmf\Builder\FormType\SelectOther\SelectOther;
use Qscmf\Builder\FormType\Self\Self_;
use Qscmf\Builder\FormType\Static_\Static_;
use Qscmf\Builder\FormType\Tags\Tags;
use Qscmf\Builder\FormType\Text\Text;
use Qscmf\Builder\FormType\Textarea\Textarea;

trait FormTypeRegister{
    private $_form_type = [];

    protected function registerFormType(){
        static $form_type = [];
        if(empty($form_type)) {
            $base_form_type = self::registerBaseFormType();
            $form_type = array_merge($base_form_type, RegisterContainer::getFormItems());
        }

        $this->_form_type = $form_type;
    }

    protected function registerBaseFormType(){
        return [
            'address' => Address::class,
            'array' => Arr::class,
            'board' => Board::class,
            'checkbox' => Checkbox::class,
            'city' => City::class,
            'citys' => Citys::class,
            'date' => Date::class,
            'datetime' => Datetime::class,
            'district' => District::class,
            'districts' => Districts::class,
            'editormd' => Editormd::class,
            'file' => File::class,
            'files' => Files::class,
            'hidden' => Hidden::class,
            'icon' => Icon::class,
            'key' => Key::class,
            'num' => Num::class,
            'password' => Password::class,
            'picture' => Picture::class,
            'picture_intercept' => PictureIntercept::class,
            'pictures' => Pictures::class,
            'pictures_intercept' => PicturesIntercept::class,
            'province' => Province::class,
            'radio' => Radio::class,
            'select' => Select::class,
            'select2' => Select2::class,
            'select_other' => SelectOther::class,
            'self' => Self_::class,
            'static' => Static_::class,
            'tags' => Tags::class,
            'text' => Text::class,
            'textarea' => Textarea::class
        ];
    }
}