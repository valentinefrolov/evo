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

class Time extends FormField
{
    public $selectMonthClass = 'picker__select--month no-chosen';
    public $selectYearClass = 'picker__select--year no-chosen';
    public $yearCount = '100';
    public $max = 'true';
    public $min = 'false';
    public $showCurrent = true;

    protected function html()
    {
        if(is_bool($this->max)) {
            $this->max = (int)$this->max;
        }

        if(is_bool($this->min)) {
            $this->min = (int)$this->min;
        }

        if(is_bool($this->yearCount)) {
            $this->yearCount = (int)$this->yearCount;
        }

        $this->inputAttributes['type'] = 'text';


        Evo::app()->view->registerScriptSrc('/asset/datetimepicker/build/jquery.datetimepicker.full.js', "jquery", 'datetimepicker-js');
        Evo::app()->view->registerInlineScript('$.datetimepicker.setLocale("'.strtolower($this->lang->getLocale()).'");', 'datetimepicker-js', 'time-locale-setter-'.$this->inputAttributes['id']);
        Evo::app()->view->addStyle('<link rel="stylesheet" type="text/css" href="/asset/datetimepicker/build/jquery.datetimepicker.min.css">', 'datetimepicker-css');

        $this->registerInlineScript("
                var picker = $('#{$this->inputAttributes['id']}').datetimepicker({datepicker:false, format:'H:i:s'});
            ", 'datetimepicker-js', 'datetimepicker'.$this->inputAttributes['id']);



        /*if($this->showCurrent) {
            Evo::app()->view->addScript("
                var picker = $('#{$this->inputAttributes['id']}')
                            .pickadate({formatSubmit: 'yyyy-mm-dd', selectMonth: true, selectYears: $this->yearCount, max: $this->max, 'min': $this->min, klass: {selectMonth: '$this->selectMonthClass', selectYear: '$this->selectYearClass'}})
                            .pickadate('picker')
                            .set('select', '{$this->value}', { format: 'yyyy-mm-dd'});
                            $('#{$this->inputAttributes['id']}').pickatime();
            ");
        } else {
            Evo::app()->view->addScript("
                var picker = $('#{$this->inputAttributes['id']}')
                            .pickadate({formatSubmit: 'yyyy-mm-dd', selectMonth: true, selectYears: $this->yearCount, max: $this->max, 'min': $this->min, klass: {selectMonth: '$this->selectMonthClass', selectYear: '$this->selectYearClass'}});
                            $('#{$this->inputAttributes['id']}').pickatime();
            ");
        }*/

        return $this->input($this->inputAttributes);
    }
}