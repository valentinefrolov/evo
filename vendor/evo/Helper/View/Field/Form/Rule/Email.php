<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 16.06.16
 * Time: 14:03
 */

namespace Evo\Helper\View\Field\Form\Rule;


class Email extends Regexp
{
    protected $param = '/^(([^<>()\[\]\.,;:\s@"]+(\.[^<>()\[\]\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/';
}