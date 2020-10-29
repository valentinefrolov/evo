<?php


namespace Evo\Rule;

use Evo;
use Evo\Interfaces\FieldEntity;
use Evo\Rule;
use Evo\Interfaces\ViewField;
use Gregwar\Captcha\CaptchaBuilder;

class Captcha extends Rule
{
    const AJAX_ID = 'reloadCaptcha';

    /** @var CaptchaBuilder */
    public $builder = null;
    public $id = 'captcha';

    private static $instances = [];

    public function __construct(FieldEntity $field, array $params = null)
    {
        parent::__construct($field, $params);

        if(!empty(static::$instances[$this->id])) {
            $this->builder = static::$instances[$this->id];
        } else {
            $this->builder = new CaptchaBuilder();
            static::$instances[$this->id] = $this->builder;
        }

        if(Evo::app()->request->ajax && Evo::app()->request->get(static::AJAX_ID) == $this->id) {
            $data = Evo::app()->request->session("captcha_phrase.$this->id");
            $this->builder->build($data['width'], $data['height']);
            Evo::app()->request->session("captcha_phrase.$this->id.phrase", $this->builder->getPhrase());
            Evo::app()->view->returnAjax($this->builder->inline());
            //return Evo::app()->view->returnAjax($this->builder->inline());
        }

    }

    protected function check()
    {
        if(Evo::app()->request->session("captcha_phrase.$this->id.phrase") != $this->field->value()) {
            $this->makeError();
        }
    }

    public function output(ViewField $field)
    {
        $this->builder->build($field->width, $field->height);
        Evo::app()->request->session("captcha_phrase.$this->id", [
            'phrase' => $this->builder->getPhrase(),
            'width' => $field->width,
            'height' => $field->height,
        ]);
        return $this->builder->inline();
    }
}