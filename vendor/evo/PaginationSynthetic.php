<?php

namespace Evo;

use Evo;
/**
 * Description of pagination
 *
 * @author frolov
 */
class PaginationSynthetic extends Pagination
{
    public function __construct(ModelDb $model, array $data, $limit, $groupWidth = null, $url = null)
    {
        parent::__construct($model, $limit, $groupWidth, $url);

        $this->model->clearData();
        $this->model->assign($data, true);

        $this->run();
    }
    
    public function run()
    {
        $count = count($this->model->data);

        $this->_itemOverall = $count;

        $this->_overall = $this->_limit ? ceil($count/$this->_limit) : $count;
        $this->_index = $this->request->get($this->url()) ? (int)$this->request->get($this->url()) - 1 : 0;

        $this->_groupIndex = floor($this->_index / $this->groupWidth);
        $this->_groupCount = ceil($this->_overall / $this->groupWidth);

        if($this->_limit) {
            $data = [];

            for($i = $this->_limit * $this->_index; $i < $this->_limit * ($this->_index + 1); $i++) {
                if($i >= count($this->model->data)) break;
                $data[] = $this->model->data[$i];
            }

            $this->model->clearData();
            $this->model->assign($data, true);


            $this->setGroupButtons();
            $this->setEndingButtons();
            $this->setSiblingButtons();
            $this->setItems();
        }

    }
}
