<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 16.06.16
 * Time: 14:03
 */

namespace Evo\Helper\View\Field\Form\Rule;


class Regexp extends Rule
{
    protected function handle()
    {
        if(!$this->param) {
            throw new \Exception("No regex declared");
        }

        return "
            function(el) {
                if(el.val() && !{$this->param}.test(el.val())) {
                    return false;
                }
                return true;
            }
        ";
    }

    protected function getAction()
    {
        return 'blur';
    }
}