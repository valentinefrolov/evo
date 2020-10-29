<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 16.06.16
 * Time: 16:01
 */

namespace Evo\Helper\View\Field\Form\Rule;

use Evo;

class Mask extends Rule
{
    protected $showMaskOnHover = 'false';

    protected function listener()
    {
        $version = '3.3.1';
        $libs = [
            'inputMask_ext' => 'inputmask.extensions',
            'inputMask_dep' => 'inputmask.dependencyLib.jquery',
            'inputMask_date' => 'inputmask.date.extensions',
            'inputMask_numeric' => 'inputmask.numeric.extensions',
            'inputMask_phone' => 'inputmask.phone.extensions',
            'inputMask_regex' => 'inputmask.regex.extensions',
        ];

        $this->registerScriptSrc("/asset/jquery.inputmask/$version/min/inputmask/inputmask.min.js", 'jquery', 'inputmask');
        $this->registerScriptSrc("/asset/jquery.inputmask/$version/min/inputmask/jquery.inputmask.min.js", 'inputmask', 'inputMask_jQuery');

        foreach($libs as $library => $src) {
            $this->registerScriptSrc("/asset/jquery.inputmask/$version/min/inputmask/$src.min.js", 'inputMask_jQuery', $src);
        }

        $this->registerInlineScript("$('$this->_id').inputmask('{$this->param}', {showMaskOnHover:$this->showMaskOnHover});", ['inputMask_jQuery', 'AplexFormValidator']);

        /*return "
            function(el) {
                el.inputmask('{$this->param}', {showMaskOnHover:$this->showMaskOnHover});
            }
        ";*/
    }

    protected function handle()
    {
        return "
            function(el) {
                if(!el.val()) {
                    return true;
                }
                return el.inputmask('isComplete');
            }
        ";
    }

    protected function getAction()
    {
        return 'blur';
    }
}