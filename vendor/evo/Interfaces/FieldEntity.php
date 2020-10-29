<?php


namespace Evo\Interfaces;

use Evo\Model;

/**
 *
 * @author frolov
 */
interface FieldEntity {
    
    function __construct(Model $model, $name, $title);
    function title(); //returns a title
    function value($value=null); //returns a value
    function data($data=null); //returns a value
    function addRule($name, $config); //returns a value
    function validate(); //returns a value
}
