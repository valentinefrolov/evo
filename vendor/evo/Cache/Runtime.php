<?php


namespace Evo\Cache;

use Evo;

class Runtime implements CacheInterface
{
    /** @var array  */
    private static $storage = [];
    
    public function __construct()
    {
        if(static::$storage === null) {
            static::$storage = [];
        }
    }

    function get(string $name)
    {
        if(!empty(static::$storage[$name])) {
            return static::$storage[$name];
        }
        return null;
    }

    function set(string $name, $data)
    {
        static::$storage[$name] = $data;
        return true;
    }

    function has(string $name): bool
    {
        if(!empty(static::$storage[$name])) {
            return true;
        }
        return false;
    }

    function delete(string $name): int
    {
        if(!empty(static::$storage[$name])) {
            unset(static::$storage[$name]);
            return true;
        }
        return false;

    }

    function clear($name = null): int
    {
        static::$storage = [];
        return true;
    }

    function getAll(): array
    {
        return static::$storage;
    }
}