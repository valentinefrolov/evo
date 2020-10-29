<?php

namespace Evo\Interfaces;

interface Module
{
    function __construct($config);
    function render($content); // вывод
    function load(); // загрузка блоков / компонентов
    function getController(); // возвращает объект контроллера
    function getAction(); // возращает название метода контроллера
    function getUrlOffset(); // возращает название метода контроллера
    function path(); // возращает название метода контроллера
    function getConfig(); // возращает название метода контроллера
}