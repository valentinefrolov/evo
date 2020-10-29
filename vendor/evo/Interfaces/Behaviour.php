<?php

namespace Evo\Interfaces;

interface Behaviour
{
    public function beforeAction();
    public function action();
    public function afterAction();
}