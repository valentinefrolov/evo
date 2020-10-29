<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 16.06.16
 * Time: 16:35
 */

namespace Evo\Helper\View\Field\Form\Rule;


class RequiredCase extends Rule
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
                var failed = objects.length ? true : false;
                $.each(objects, function(k,obj){
                    if(obj.val() && obj.val() !== '0') {
                        failed = false;
                        return false;
                    }
                });

                if(el.attr('type') == 'radio' || el.attr('type') == 'checkbox') {
                    if(!el.filter('input:checked').val() && failed) {
                        return false;
                    }
                } else if(!el.val() && failed) {
                    return false;
                }
                return true;
            }
        ";
    }

    protected function getAction()
    {
        return "blur";
    }
}