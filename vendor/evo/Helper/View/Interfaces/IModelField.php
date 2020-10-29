<?php


namespace Evo\Helper\View\Interfaces;


use Evo\Helper\View\Field\ModelField;

interface IModelField
{
    function __construct(ModelField $field);
    function getWrapper() : string;
    function getLabel() : string;
    function getError(array $errors) : string;
}