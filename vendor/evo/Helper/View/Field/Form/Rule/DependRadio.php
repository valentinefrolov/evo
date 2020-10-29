<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 17.06.16
 * Time: 12:10
 */

namespace Evo\Helper\View\Field\Form\Rule;

use Evo;

class DependRadio extends Rule
{
    protected $field = null;
    protected $value = null;

    protected function handle()
    {
        if(!$this->field || !$this->value) {
            throw new \Exception("No field or no value declared in view rule 'DependRadio'");
        }

        $id = $this->_field->model->getField($this->field)['id'];


        $function = "
            function(el) {
                var checked = el.filter('input:checked');
                if(checked.val() != '$this->value') {
                    $('#{$id}').attr('readonly', true);
                    $('#{$id}').val('');
                } else {
                    $('#{$id}').removeAttr('readonly');
                }
                return true;
            }
        ";

        Evo::app()->view->addScript("({$function})($({$this->_selector}));");

        return $function;

    }

    protected function getAction()
    {
        return 'change';
    }

    /*protected function getId()
    {
        $arr = [];
        foreach(array_keys($this->_field->data) as $id) {
            $arr[] = '#'.$this->_field->id.'_'.$id;
        }

        return implode(', ', $arr);
    }*/
}