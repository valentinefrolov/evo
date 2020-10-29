<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 09.03.16
 * Time: 11:03
 */

namespace Evo\Helper\View\Field\Form;

use Evo\Helper\View\Field\FormField;
use Evo;

class Date extends FormField
{
    public $selectMonthClass = 'picker__select--month no-chosen';
    public $selectYearClass = 'picker__select--year no-chosen';
    public $yearCount = '100';
    public $max = 'true';
    public $min = 'false';
    public $showCurrent = true;

    protected function html()
    {
        if(is_bool($this->yearCount)) {
            $this->yearCount = (int)$this->yearCount;
        }

        if($this->min === true) {
            $this->min = 'true';
        }

        if($this->min === false) {
            $this->min = 'false';
        }

        if($this->max === true) {
            $this->max = 'true';
        }

        if($this->max === false) {
            $this->max = 'false';
        }

        if($this->max != 'true' && $this->max != 'false') {
            $max = date('Y, m, d', strtotime($this->max . ' -1 month'));
            $this->max = "new Date($max)";
        }

        if($this->min != 'true' && $this->min != 'false') {
            $min = date('Y, m, d', strtotime($this->min . ' -1 month'));
            $this->min = "new Date($min)";
        }

        $this->inputAttributes['type'] = 'text';
        $this->inputAttributes['autocomplete'] = 'off';
        $this->inputAttributes['value'] = $this->inputAttributes['value'] == '0000-00-00' ? '' : $this->inputAttributes['value'];

        Evo::app()->view->addStyle('<link rel="stylesheet" type="text/css" href="/asset/datepicker/themes/classic.css"/>', 'picker-css');

        Evo::app()->view->addStyle('<link rel="stylesheet" type="text/css" href="/asset/datepicker/themes/classic.date.css"/>', 'date-picker-css');

        $this->registerScriptSrc('/asset/datepicker/picker.js', 'jquery', 'picker-js');
        $this->registerScriptSrc('/asset/datepicker/picker.date.js', 'picker-js', 'date-picker-js');
        $this->registerScriptSrc('/asset/datepicker/translations/'.$this->lang->getLocale('f').'.js', 'date-picker-js', 'date-picker-lang-'.$this->lang->getLocale());



        $this->registerInlineScript("
            var picker{$this->inputAttributes['id']} = $('#{$this->inputAttributes['id']}')
                        .pickadate({formatSubmit: 'yyyy-mm-dd', selectMonth: true, selectYears: $this->yearCount, max: $this->max, min: $this->min, klass: {selectMonth: '$this->selectMonthClass', selectYear: '$this->selectYearClass'}})
                        .pickadate('picker');

        ", 'date-picker-js', 'date-picker'.$this->inputAttributes['id']);


        if($this->showCurrent || $this->value) {
            Evo::app()->view->registerInlineScript("
                if(picker{$this->inputAttributes['id']}) 
                    picker{$this->inputAttributes['id']}.set('select', '$this->value', { format: 'yyyy-mm-dd'});
            ", 'date-picker'.$this->inputAttributes['id'], 'date-picker'.$this->inputAttributes['id'].'-setter');
        }

        return $this->input($this->inputAttributes);
    }
}