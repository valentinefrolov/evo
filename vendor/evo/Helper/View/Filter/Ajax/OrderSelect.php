<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 04.03.16
 * Time: 11:04
 */

namespace Evo\Helper\View\Filter\Ajax;




class OrderSelect extends OrderClick
{
    protected function html()
    {
        return $this->div($this->ajax()
            ->pushState(true)
            ->refresh()
            ->block()
            ->url($this->locator->route($this->request->route(), [$this->filter->getName() => $this->quote("$(this).val()")], null, true))
            ->select($this->getOptions()), ['class' => 'filter-order-select']);
    }

    protected function getOptions()
    {
        $var = [
            '' => '',
            'ASC' => $this->lang->t('common.ascending'),
            'DESC' => $this->lang->t('common.descending'),
        ];
        $options = '';
        foreach($var as $val => $t) {
            $opt = ['value' => $val];
            if($this->filter->value == $val) {
                $opt = ['selected' => 'selected'];
            }
            $options .= $this->option($t, $opt) . PHP_EOL;
        }

        return $options;
    }
}