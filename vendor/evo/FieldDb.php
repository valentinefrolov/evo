<?php

namespace Evo;

use Evo;

/**
 * Description of fieldDb
 *
 * @author frolov
 */
class FieldDb extends Field
{
    public $alias = '';
    public $origin = '';
    public $via = '';
    public $relatePrimary = '';

    public $filterNS = 'Evo\Filter\\';
    
    public function __construct(Model $model, $name, $title=null)
    {
        parent::__construct($model, $name, $title);
        $this->origin = $name;
        $this->alias = $this->model->getFieldAlias($name);
    }

    public function getTableName()
    {
        return substr($this->alias, 0, strpos($this->alias, '.'));
    }


}
