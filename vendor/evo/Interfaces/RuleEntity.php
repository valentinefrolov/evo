<?php

namespace Evo\Interfaces;

use Evo\Interfaces\FieldEntity;
use Evo\Interfaces\ViewField;
use Evo\Field;

/**
 * Определяет поведение правил
 * у каждой модели есть поля и правила для них
 * у кадого поля может быть несколько валидаторов
 * Правило объединяет в себе поле и валидатор и добавляет себя к полю
 * @author frolov
 */
interface RuleEntity {
    function __construct(FieldEntity $field, array $param = []);
    function output(ViewField $field); // обработчик перед выводом
    function input($val); // обработчик входных данных
    function validate();
    function getError();
}