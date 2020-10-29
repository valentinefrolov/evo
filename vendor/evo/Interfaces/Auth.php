<?php

namespace Evo\Interfaces;


interface Auth
{
    public function checkAuth();
    public function login(AuthModel $model);
    public function logout(AuthModel $model);
    public function isLogged();
    public function getUser();
}