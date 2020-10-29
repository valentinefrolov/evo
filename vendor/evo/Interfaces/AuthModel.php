<?php

namespace Evo\Interfaces;

interface AuthModel
{
    function getUserRole();
    function roleExists($roleName);
    function roles();
}