<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 14.10.16
 * Time: 18:15
 */

namespace Evo\Helper\View\Field\Form\Rule;


class RequiredCheckbox extends Required
{
    protected function handle()
    {
        return "
            function(el) {
                if(el[0].checked) {
                    return true;
                }
                return false;
            }
        ";
    }
}