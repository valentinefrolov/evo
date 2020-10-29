<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 15.03.16
 * Time: 18:36
 */

namespace Evo\Helper\View\Field\Form;

use Evo;
use Evo\Helper\View\Field\FormField;
use Evo\Helper\View\Model as ViewModel;

abstract class Manage extends FormField
{
    public $action = '';
    public $confirm = '';
    public $dynamic = false;
    public $url = '';
    public $click = '';
    public $valid = false;

    protected $template = '<div class="form-row submit">{html}</div>';


    public function handle()
    {
        $this->inputAttributes['name'] = preg_replace('/\[([^\]]+)\]$/', '[_$1]', $this->inputAttributes['name']);

        if($this->click) {

            $action = str_replace('"', '&quot;', 'e.preventDefault();'.$this->click);
        } else {
            $action = $this->action ?
                "$('#{$this->inputAttributes['id']}').closest('form').attr('action', '$this->action');"
                :
                ($this->url ?
                    "window.location.href='$this->url'; return false;" :
                    "var input = $('<input/>', {type:'hidden', name: '{$this->inputAttributes['name']}', value: '".(isset($this->inputAttributes['title'])?$this->inputAttributes['title']:'1')."'}); $(this).append(input);"
                );
        }

        $prop = $this->valid ? 'e.stopPropagation();' : '';

        $script = "
                e.preventDefault();
                var form = $(this).closest('form');
                var input = $(this);
                 
                form.off('submit').on('submit', function(e){
                    if(form.data('check') === undefined || form.data('check')(e)) {
                        $prop
                        $action
                    }
                });
                
                if(true".($this->confirm ? ' && confirm(\''.$this->confirm.'\')':'').") {
                    form.submit();
                }";
        if($this->dynamic) {
            Evo::app()->view->registerInlineScript("$('#{$this->inputAttributes['id']}').click(function(e){{$script}});", '*');
        } else {
            Evo::app()->view->registerInlineScript("$(document).on('click', '#{$this->inputAttributes['id']}', function(e){{$script}});", '*', $this->inputAttributes['id']);
        }
        $res = parent::handle();

        return $res;
    }


}