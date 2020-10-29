<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 04.03.16
 * Time: 11:11
 */

namespace Evo\Helper\View\Filter;

use Evo\Helper\View\Script\Tooltip;

class SearchHint extends DataFilter
{
    protected function html()
    {
        $id = $this->filter->field->model->className() .
            ucfirst($this->filter->field->name) . 'SearchHint';

        $js = $this->getHelper('js');

        $route = $js::route(null, array_merge($this->getWhereFilters(), [$this->filter->getName() => 'obj.index']));

        $callback = "function(obj){
            window.location.href = $route;
        }";

        $tooltip = $this->getHelper('Script/Tooltip');
        $tooltip::build($this->data, "$('#$id')", $callback);

        return $this->input(['type' => 'text', 'id' => $id]);
    }
}