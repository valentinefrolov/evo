<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 09.03.16
 * Time: 11:03
 */

namespace Evo\Helper\View\Field\Form;

class OptGroup extends Select
{
    protected function data()
    {
        $options = [];
        foreach(array_keys($this->data) as $label) {
            $optGroup = [];
            foreach($this->data[$label] as $id => $title) {
                $config = ['value' => $id];
                if($id == $this->value) {
                    $config['selected'] = ['selected'];
                }
                $optGroup[] = $this->option($title, $config);
            }
            $options[] = $this->optgroup(implode(PHP_EOL, $optGroup), ['label' => $label]);
        }
        return implode(PHP_EOL, $options);
    }
}