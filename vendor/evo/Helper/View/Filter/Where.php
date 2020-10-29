<?php

namespace Evo\Html\Filter;

use Evo\Filter\Where as BaseWhere;
use Evo\Html;
use Evo\Lang;

/**
 * Description of where
 *
 * @author frolov
 */
class Where {
    
    private $filter = null;
    
    public function __construct(BaseWhere $where)
    {    
        $this->filter = $where;
    }
    
    public function html($canBeEmpty = false)
    {        
        $options = [];
        
        if($canBeEmpty) {
            $props = [];
            $props['value'] = 0;
            if(!$this->filter->value) $props['selected'] = 'selected';
            $options[] = Html::option(Lang::t('common.empty'), $props);
        }
        foreach($this->filter->field->data() as $key => $value) {
            $props = [];
            $props['value'] = $key;
            if($this->filter->value == $key) $props['selected'] = 'selected';
            $options[] = Html::option($value, $props);
        }
        
        return Html::select(implode($options), ['name' => $this->filter->getName()]);
    }
}
