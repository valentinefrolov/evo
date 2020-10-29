<?php

namespace Evo\Interfaces;

interface ModuleEntity
{
    function __construct($config, $name);
    function action();
    function init();
    function getController();
    function getPath();
    function getUrl();
    function getLayout();
}