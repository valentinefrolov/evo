<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 25.03.16
 * Time: 9:43
 */

namespace Evo\Helper\View\Field\Form;

use Evo\Helper\View\Field\FormField;


class Captcha extends FormField
{
    public $width = 150;
    public $height = 40;
    public $url = null;

    protected function html()
    {
        //\Evo\Debug::dump($this->field->getRule('captcha')->builder->inline());
        /** @var \Evo\Rule\Captcha $rule */
        $rule = $this->field->getRule('captcha');

        $captcha = [
            'src' => $rule->output($this),
            'alt' => 'captcha',
            'id' => $this->inputAttributes['id'].'Image',
            'data-captcha-id' => $rule->id
        ];

        $this->inputAttributes['type'] = 'text';


        $ajax = $this->ajax()->data([$rule::AJAX_ID => $rule->id])->success("function(src){
            $('[data-captcha-id=\"'+$('#{$this->inputAttributes['id']}Image').attr('data-captcha-id')+'\"]').attr('src', src);
        }");

        if($this->url) {
            $ajax->url($this->url);
        }


        $this->registerInlineScript("
        
            $('#{$this->inputAttributes['id']}Image').click(function(){
                $ajax
            });
        
        ", 'jquery', 'captcha'.$this->inputAttributes['id']);

        return $this->img($captcha) . $this->input($this->inputAttributes);
    }

    protected function ruleRequired()
    {
        return 'captcha';
    }
}