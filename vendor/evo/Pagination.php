<?php

namespace Evo;

use Evo;
/**
 * Description of pagination
 *
 * @author frolov
 */
class Pagination 
{
    public $prefix     = 'page';

    public $groupWidth  = 8; // ширина группы
    public $sibling     = true; // кнопки переключения внутри группы
    public $group       = true; // кнопки переключения групп
    public $ending      = true; // кнопки начала и конца
    public $request     = null;

    public $model      = null;

    protected $_keep        = false;
    protected $_reset       = false;
    protected $_url         = '';
    protected $_storage     = null;
    protected $_limit       = 0;
    protected $_overall     = 0; // кол-во элементов
    protected $_index       = 0;
    protected $_groupIndex  = 0;
    protected $_groupCount  = 0;
    protected $_select      = null;
    protected $_state       = null;
    protected $_itemOverall = null;
    protected $template     = 'ending.left,group.left,sibling.left,items,sibling.right,group.right,ending.right';
    
    public function __construct(ModelDb $model, $limit, $groupWidth = null, $url = null, $width=null)
    {
        $this->model = $model;
        $this->request = Evo::app()->request;

        $this->_url = $url ? $url : $this->model->name().'_'.$this->prefix;

        $this->_limit = is_numeric($limit) ? $limit : 0;

        if($groupWidth) {
            $this->groupWidth = $groupWidth;
        }

        $this->_storage = new Storage();

    }
    
    public function run()
    {
        $this->model->select();

        $count = count(Evo::app()->db()->fetchCol($this->model->select));

        $this->_itemOverall = $count;

        $this->_overall = $this->_limit ? ceil($count/$this->_limit) : $count;
        $this->_index = $this->request->get($this->url()) ? (int)$this->request->get($this->url()) - 1 : 0;

        if($this->_limit) {
            if(!$this->_keep) {
                //echo $this->_limit . ' ' . $this->_index;
                $this->model->limit($this->_limit, ($this->_index* $this->_limit));
            } else {
                $this->model->limit($this->_limit * ($this->_index));
            }
        }

        $this->_groupIndex = floor($this->_index / $this->groupWidth);
        $this->_groupCount = ceil($this->_overall / $this->groupWidth);

        if($this->_limit) {
            $this->setGroupButtons();
            $this->setEndingButtons();
            $this->setSiblingButtons();
            $this->setItems();
        }

        //Evo::app()->request->delete($this->url());
    }
    
    protected function setItems()
    {
        $start = $this->_groupIndex*$this->groupWidth+1;
        
        $check = ($this->_groupIndex + 1)*$this->groupWidth;
        $end = $check > $this->_overall ? $this->_overall : $check;

        $this->_state = $end - $start > 0 ? true : false;
        
        $buttons = [];
        for($i=$start;$i<=$end;$i++) {
            $buttons[$i] = $i;
        }
        $this->_storage->set('buttons.items', $buttons);
        
    }

    protected function setSiblingButtons() 
    {
        if($this->_index > 0){
            $this->_storage->set('buttons.sibling.left', $this->_index);
        }
        
        if($this->_index < $this->_overall - 1){
            $this->_storage->set('buttons.sibling.right', $this->_index + 2);
        }
    }
    
    protected function setEndingButtons()
    {
        if($this->_index < ($this->_groupCount-2)*$this->groupWidth) {
            // последний элемент
            $this->_storage->set('buttons.ending.right', $this->_overall);
        }
        
        if($this->_index > $this->groupWidth*2-1){
            $this->_storage->set('buttons.ending.left', 1);
        }
    }
    
    protected function setGroupButtons() 
    {
        if($this->_groupIndex > 0) {
            // последний элемент предыдущей группы
            $this->_storage->set('buttons.group.left', $this->_groupIndex*$this->groupWidth);
        }

        if($this->_groupIndex + 1 < $this->_groupCount) {
            // первый элемент следующей группы
            $this->_storage->set('buttons.group.right', ($this->_groupIndex+1)*$this->groupWidth+1);
        }
    }
    
    public function url()
    {
        return $this->_url;
    }
    
    public function get($address)
    {        
        return $this->_storage->get('buttons.'.$address);
    }
    
    public function index()
    {
        return $this->_index + 1;
    }

    public function indexExist($index) {
        if($index >= 1 && $index <= $this->_overall) {
            return true;
        }
        return false;
    }

    public function itemIndex()
    {
        return count($this->model);
    }

    public function itemLast()
    {
        return $this->_itemOverall;
    }
    
    public function last()
    {
        return $this->_overall;
    }
    
    public function getState()
    {
        return $this->_state;
    }

    public function template($template)
    {
        $this->template = $template;
    }

    public function getButtons($template=null)
    {
        $template = $template ? $template : $this->template;
        $buttons = array();
        foreach(explode(',', $template) as $item){
            $item = preg_replace('/\s+/', '', $item);
            $buttons[$item] = $this->_storage->get("buttons.$item");
        }
        return $buttons;
    }

    public function keepViewed($keep=true)
    {
        $this->_keep = $keep;
    }

    public function getLimit()
    {
        return $this->_limit;
    }
}
