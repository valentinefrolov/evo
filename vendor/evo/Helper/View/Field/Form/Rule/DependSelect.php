<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 17.06.16
 * Time: 12:10
 */

namespace Evo\Helper\View\Field\Form\Rule;

use Evo;

class DependSelect extends DependRadio
{
    protected $field = null;
    protected $value = null;

    protected function handle()
    {
        if(!$this->field || !$this->value) {
            throw new \Exception("No field or no value declared in validator 'Depend'");
        }

        $dependField = $this->_field->model->getField($this->field);


        $idSelf = $this->_field['id'];
        $idDepend = $dependField['id'];

        $function = "
            $('#$idDepend').find(input).change(function(){
                $('#$idSelf).val('');
            });
            function(el) {
                if($('$idSelf').val() != '$this->value') {
                    $('#{$idDepend}').find(input).prop('checked', false);
                }
                return true;
            }
        ";



        Evo::app()->view->addScript("({$function})();");

        return $function;

    }

    protected function getAction()
    {
        return 'change';
    }

}