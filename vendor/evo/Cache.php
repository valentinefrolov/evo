<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 02.08.2017
 * Time: 16:35
 */

namespace Evo;

use Evo\Exception\PermissionException;

use Evo\Cache\CacheInterface;
use Evo\Cache\APCU;
use Evo\Cache\File;
use Evo\Cache\Runtime;

class Cache implements CacheInterface
{
    /** @var CacheInterface */
    private static $defaultEngine = null;

    /** @var CacheInterface */
    private $engine = null;
    /** @var string  */
    private $prefix = '';


    public static function configure(string $engineName = null) : CacheInterface
    {
        $engineName = strtolower($engineName);

        switch($engineName) {
            case 'none':
            case 'runtime':
                $engine = new Runtime();
                break;
            case 'file':
                try {
                    $engine = new File();
                } catch(PermissionException $e) {
                    $engine = new Runtime();
                }
                break;
            case null:
            case 'apcu':
            default:
                if(function_exists('apcu_cache_info')) {
                    $engine = new APCU();
                } else {
                    $engine = new Runtime();
                }
        }
        if(!static::$defaultEngine) {
            static::$defaultEngine = $engine;
        }
        return $engine;
    }

    /**
     * Cache constructor.
     * @param string $prefix
     * @param string|null $engine
     */
    public function __construct(string $prefix, string $engine = null)
    {
        $this->prefix = \Evo::getConfig('app')['host'].str_replace('#', '_', $prefix) . '_';
        $this->engine = $engine === null && static::$defaultEngine ? static::$defaultEngine : static::configure($engine);
    }

    function get(string $name)
    {
        return $this->engine->get($this->prefix.$name);
    }

    function set(string $name, $data)
    {
        return $this->engine->set($this->prefix.str_replace('#', '_', $name), $data);
    }

    function has(string $name): bool
    {
        return $name && $this->engine->has($this->prefix.$name);
    }

    function delete(string $name): int
    {
        return $this->engine->delete($this->prefix.$name);
    }

    function clear($name = null): int
    {
        if($name === true) {
            return $this->engine->clear(\Evo::getConfig('app')['host']);
        }
        return $this->engine->clear($this->prefix);
    }

    function getAll(): array
    {
        return $this->engine->getAll();
    }
}