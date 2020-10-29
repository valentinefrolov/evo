<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 10.10.16
 * Time: 12:22
 */

namespace Evo\Helper\View\Field\Form\Rule;


class Same extends Rule
{
    protected function handle()
    {
        $objects = '';

        foreach(preg_split('/\s*,\s*/', $this->param) as $name) {
            $id = $this->_field->model->getField($name)['id'];
            $name = $this->_field->model->getField($name)['name'];

            $objects .= "objects.push($('#{$id}').length === 1 ? $('#{$id}') : $('[name=\"{$name}\"]:checked'));".PHP_EOL;
        }

        return "
            function(el) {
                var objects = [];
                $objects
                var fail = false;
                $.each(objects, function(k,obj){
                    if(obj.val() != el.val()) {
                        fail = true;
                        return false;
                    }
                });
                return !fail;
            }
        ";
    }

    protected function getAction()
    {
        return 'keyup';
    }
}