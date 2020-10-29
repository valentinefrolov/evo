<?php

namespace Evo;

use ArrayObject;
use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
/**
 * Description of ModelData
 *
 * @author frolov
 */
class Data extends ArrayObject implements ArrayAccess, IteratorAggregate
{
    private $_offset = 0;
    
    public $data = [];
    public $original = [];
    public $canBeNull = false;
    public $sortField = null;

    public $nullIndex = -1; // 0 - первый элемент, 1 - последний

    public function canBeNull($text=null,$toEnd=false)
    {
        if(!$text) {
            $this->canBeNull = !$this->canBeNull;
        } else {
            $this->canBeNull = $text;
        }

        if(!$this->canBeNull) {
            $this->nullIndex = -1;
        } else {
            $this->nullIndex = $toEnd ? 1 : 0;
        }
        return $this;
    }
    
    public function offsetSet($name, $value) 
    {
        $this->data[$name] = $value;
        if($this->sort) {
            $this->sort[$name] = $value;
        }
    }

    public function offsetExists($name) 
    {
        return isset($this->data[$name]);
    }

    public function offsetUnset($name) 
    {
        unset($this->data[$name]);
    }
    
    public function offsetGet($name) 
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }
    
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }


    public function unsetOffset()
    {
        $this->_offset = 0;
    }
    
    public function count()
    {     
        return count($this->data);
    }
    
    public function current()
    {
        $counter = 0; 
        foreach($this->data as $row) {
            if($counter == $this->_offset) {
                $this->_offset++;
                return $row;
            }
            $counter++;
        }
        
        $this->_offset = 0;
        return false;
    }
    
    public function key()
    {
        $counter = 0;
        foreach(array_keys($this->data) as $key) {
            if($counter == $this->_offset) {
                $this->_offset++;
                return $key;
            }
            $counter++;
        }
        
        $this->_offset = 0;
        return false;
    }
    



    protected function _array($array)
    {

        if($this->canBeNull) {
            $text = $this->canBeNull === true ? '' : $this->canBeNull;
            $this->nullIndex ? $array = $array + [null => $text] : $array = [null => $text] + $array;
        }



        $this->sortField ? $this->data[$this->sortField] = $array : $this->data = $array;
        $this->sortField = null;
        return $this->data;
    }

    public function unique()
    {
        $this->original = $this->original ? $this->original : $this->data;
        $data = $this->sortField ? $this->original[$this->sortField] : $this->original;
        $res = array_unique($data, SORT_REGULAR);
        return $this->_array($res);
    }

    /**
     * @param $value
     * @param $key
     * @return array
     */
    public function pairs($value, $key)
    {
        $this->original = $this->original ? $this->original : $this->data;
        
        $res = [];

        $data = $this->sortField ? $this->original[$this->sortField] : $this->original;

        foreach($data as $row) {
            $res[$row[$key]] = is_callable($value) ? $value($row) : $row[$value];
        }

        return $this->_array($res);
    }

    public function values($key)
    {
        $this->original = $this->original ? $this->original : $this->data;

        $res = [];

        $data = $this->sortField ? $this->original[$this->sortField] : $this->original;

        foreach($data as $row) {
            $res[] = $row[$key];
        }

        return $this->_array($res);
    }

    
    public function groupPairs($label, $value, $key)
    {
        $this->original = $this->original ? $this->original : $this->data;
        
        $res = [];

        $data = $this->sortField ? $this->original[$this->sortField] : $this->original;
        
        foreach($data as $row) {
            if(!isset($res[$row[$label]])) {
                $res[$row[$label]] = [];
            }
            $res[$row[$label]][$row[$key]] = $row[$value];
        }

        return $this->_array($res);
    }
    
    public function sortToArray($key)
    {
        $this->original = $this->original ? $this->original : $this->data;
        
        $res = [];
        
        if($this->canBeNull) {
            $res[0] = null;
        }
        
        $data = $this->sortField ? $this->original[$this->sortField] : $this->original;
        
        foreach($data as $row) {
            
            $name = $row[$key] ? $row[$key] : 0;
            
            if(!isset($res[$name])) {
                $res[$name] = [];
            }
            $res[$name][] = $row;
        }
        return $this->_array($res);
    }
    
    public function origin()
    {
        $this->data = $this->original;
        return $this->data;
    }

    public function compare($oldData, $newData = false) : array
    {
        $old = [];
        $new = [];

        if($newData === false) {
            $newData = $this->data;
        } else if(!$newData) {
            $newData = [];
        }

        foreach($oldData as $key => $item) {
            if(false === ($index = array_search($item, $newData))) {
                // not found
                $old[$key] = $item;
            }
        }

        foreach($newData as $key => $item) {
            if(false === ($index = array_search($item, $oldData))){
                $new[$key] = $item;
            }
        }

        return [
            $old,
            $new
        ];

    }


    
}
