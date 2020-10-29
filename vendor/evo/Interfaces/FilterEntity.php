<?php

namespace Evo\Interfaces;

use Evo\Model;
use Evo\Interfaces\FieldEntity;
/**
 *
 * @author frolov
 */
interface FilterEntity 
{    
    function __construct(FieldEntity $field, Model $model);
    function id();
    function sql();
}
