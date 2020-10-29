<?php


namespace Evo\Cache;


interface CacheInterface
{
    function get(string $name);
    function set(string $name, $data);
    function has(string $name) : bool;
    function delete(string $name) : int;
    function clear($name = null) : int;
    function getAll() : array;
}