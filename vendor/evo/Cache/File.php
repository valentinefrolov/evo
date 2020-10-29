<?php


namespace Evo\Cache;


use Evo\Exception\PermissionException;

class File implements CacheInterface
{
    /** @var string */
    private $tmpDir = '';

    /**
     * File constructor.
     * @throws PermissionException
     */
    public function __construct()
    {
        $this->tmpDir = sys_get_temp_dir() . '/cache/';
        if(!is_dir($this->tmpDir)) {
            if(!mkdir($this->tmpDir)) {
                throw new PermissionException("Can't create folder {$this->tmpDir}");
            }
        }

        //echo $this->tmpDir; die();
    }

    function get(string $name)
    {
        $name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $name);
        if(file_exists($this->tmpDir.'/'.$name)) {
            return unserialize(file_get_contents($this->tmpDir.'/'.$name));
        }
        return null;
    }

    function set(string $name, $data)
    {
        $name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $name);
        return file_put_contents($this->tmpDir.'/'.$name, serialize($data));
    }

    function has(string $name): bool
    {
        $name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $name);
        if(file_exists($this->tmpDir.'/'.$name)) {
            return true;
        }
        return false;
    }

    function delete(string $name): int
    {
        $name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $name);
        if(file_exists($this->tmpDir.'/'.$name)) {
            return unlink($this->tmpDir.'/'.$name);
        }
        return false;
    }

    function clear($name = null): int
    {
        if ($handle = opendir($this->tmpDir)) {
            while (false !== ($entry = readdir($handle))) {
                if(strpos($entry, $name) === 0) {
                    unlink($this->tmpDir.'/'.$entry);
                }
            }
            closedir($handle);
            return true;
        }

        return rmdir($this->tmpDir);
    }

    function getAll(): array
    {
        $data = [];
        if ($handle = opendir($this->tmpDir)) {
            while (false !== ($entry = readdir($handle))) {
                if($entry != '.' && $entry != '..') {
                    $data[$entry] =  unserialize(file_get_contents($this->tmpDir.'/'.$entry));
                }
            }
            closedir($handle);
        }
        return $data;
    }
}