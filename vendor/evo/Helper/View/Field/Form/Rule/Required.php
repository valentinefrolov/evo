<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 16.06.16
 * Time: 16:35
 */

namespace Evo\Helper\View\Field\Form\Rule;


class Required extends Rule
{
    protected function handle()
    {
        return "
            function(el) {
                if(!el.length || !el.val()) {
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