<?php


namespace Evo;


use Evo\Exception\BehaviourException;
use Evo\Exception\StorageException;

class Storage
{
    const EMERGENCY = '^';

    /** @var string  */
    protected $name = '';
    /** @var mixed  */
    protected $value = null;
    /** @var array  */
    protected $children = [];
    /** @var Storage  */
    protected $parent = null;
    /** @var string  */
    protected $separator = '.';
    /** @var bool  */
    private $emergency = false;

    /**
     * Storage constructor.
     * @param string $separator
     * @param string $name
     * @throws BehaviourException
     */
    public function __construct(string $separator = '.', string $name = '')
    {
        $this->separator = $separator;
        if(strpos($name, $this->separator) !== false) {
            $this->separator = static::EMERGENCY;
            $this->emergency = $separator;
            if(strpos($name, $this->separator) !== false) {
                throw new BehaviourException("Storage Bind node couldn't have fully qualified name: $name");
            }
        }
        $this->name = $name;
    }

    public function set($name, $data)
    {
        if($this->emergency !== false) $name = str_replace($this->emergency, $this->separator, $name);

        if($name) {
            $names = explode($this->separator, $name);
            $node = $this;
            for($i=0; $i <count($names); $i++) {
                $node = $node->getChild($names[$i], true);
            }
            $node->setData($data);
        } else {
            $this->setData($data);
        }

    }

    public function get($name=null) {
        if($this->emergency !== false) $name = str_replace($this->emergency, $this->separator, $name);
        if($name) {
            $names = explode($this->separator, $name);
            $node = $this;
            for($i=0; $i <count($names); $i++) {
                $node = $node->getChild($names[$i]);
                if(!$node) return null;
            }
            return $node->getData();
        } else {
            return $this->getData();
        }
    }

    public function delete($name = null) {
        if($this->emergency !== false) $name = str_replace($this->emergency, $this->separator, $name);
        if($name) {
            $names = explode($this->separator, $name);
            $node = $this;
            for($i=0; $i <count($names); $i++) {
                $node = $node->getChild($names[$i]);
                if(!$node) return null;
            }
            $node->delete();
        } else {
            foreach ($this->children as $child) {
                $child->delete();
            }
            if($this->parent)
                unset($this->parent->children[$this->name]);
        }
    }


    protected function setData($data)
    {
        if(is_array($data) && $data) {
            foreach($data as $name => $value) {
                $node = $this->getChild($name, true);
                $node->setData($data[$name]);
            }
        } else {
            $this->value = $data;
            $this->children = [];
        }
    }

    protected function getData()
    {
        if($this->children) {
            $data = [];
            foreach($this->children as $child) {
                $data[$child->name] = $child->getData();
            }
            return $data;
        } else {
            return $this->value;
        }
    }

    protected function getChild($name, $create=false)
    {
        if(!empty($this->children[$name])) {
            return $this->children[$name];
        } else if ($create) {
            $child = new Storage($this->separator, $name);
            $child->parent = $this;
            $this->children[$name] = $child;
            return $child;
        }
        return false;
    }




}