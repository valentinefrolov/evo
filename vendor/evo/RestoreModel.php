<?php

namespace Evo;

use Model\Aplex\Backup;
use \Evo\Helper\View\Json;

abstract class RestoreModel extends \Evo\ModelDb
{
    protected $revisions = Backup::REVISIONS;

    // full class name => relate field
    abstract function dependency();

    // создание
    public function post($data = [], $keys = null)
    {
        if(($result = parent::post($data, $keys)) && !$this->getErrors()) {
            if($dep = $this->dependency()) {
                foreach ($dep as $modelName => $fieldName) {
                    /** @var \Evo\ModelDb $m */
                    $m = new $modelName();
                    $m->where("$fieldName = ?", $this->id())->all();
                    $dep[$modelName] = $m->data;
                }
            }
            if($dep) {
                $this->addField('_depend')->value($dep);
            }

            $backup = new Backup();

            $backup->post([
                'model' => get_class($this),
                'model_id' => $this->id(),
                'data' => Json::encode($this->data),
                'date' => date('Y-m-d H:i:s')
            ]);

            if($backup->getErrors()) {
                \Evo\Debug::dump($backup->getErrors());
            }
        }
        return $result;

    }

    // изменение
    public function put($data = [], $keys = null, $debug = false)
    {
        if(($result = parent::put($data, $keys)) && !$this->getErrors()) {
            if($dep = $this->dependency()) {
                foreach ($dep as $modelName => $fieldName) {
                    /** @var \Evo\ModelDb $m */
                    $m = new $modelName();
                    $m->where("$fieldName = ?", $this->id())->all();
                    $dep[$modelName] = $m->data;
                }
            }
            if($dep) {
                $this->addField('_depend')->value($dep);
            }

            $backup = new Backup();
            $backup->where('model = ?', get_class($this));
            $backup->where('model_id = ?', $this->id());
            $backup->order('date DESC');
            $backup->all();

            if(count($backup) < $this->revisions) {
                $backup->clear();
                $backup->post([
                    'model' => get_class($this),
                    'model_id' => $this->id(),
                    'data' => Json::encode($this->data),
                    'date' => date('Y-m-d H:i:s')
                ]);
            } else {
                $backup->assign($backup[count($backup) - 1]);
                $backup->put(['data' => Json::encode($this->data), 'date' => date('Y-m-d H:i:s')], ['data', 'date']);
            }
            if($backup->getErrors()) {
                \Evo\Debug::dump($backup->getErrors());
            }
        }
        return $result;
    }

    public function restore($id=null)
    {
        if(!$this->id()) {
            if(!$id) {
                throw new \Evo\Exception\BehaviourException('no id');
            }
            $this->id($id);
        }

        $backup = new Backup();
        $backup->where('model = ?', get_class($this));
        $backup->where('model_id = ?', $this->id());
        $backup->order('date DESC');
        $backup->limit(1, 2);

        $backup->one();

        //\Evo\Debug::dump($backup->data);

        if(!$backup->data) return false;

        $data = Json::decode($backup['data']);

        $depend = $data['_depend'];
        unset($data['_depend']);

        $b = new Backup();
        $b->where('date >= ?', $backup['date'])->delete();

        if($b->getErrors()) {
            \Evo\Debug::dump($b->getErrors());
        }

        $result = $this->one()->data ? $this->put($data) : $this->post($data);

        if($result && !$this->getErrors()) {
            $dep = $this->dependency();
            foreach ($dep as $modelName => $field) {
                $ids = [];
                if(!empty($depend[$modelName])) {
                    $data = $depend[$modelName];
                    foreach ($data as $item) {
                        /** @var \Evo\ModelDb $model */
                        $model = new $modelName();
                        $ids[] = $item[$model->primary];

                        $model->where("$field = ?", $item[$field])->where("{$model->primary} = ?", $item[$model->primary])->one()->data ?
                            $model->put($item) : $model->post($item);

                        if ($model->getErrors()) {
                            \Evo\Debug::dump($model->getErrors());
                        }
                    }
                }
                $model = new $modelName();
                $model->where("$field = ?", $this->id());
                if(count($ids)) {
                    $model->where("{$model->primary} NOT IN(?)", $ids);
                }
                $model->delete();
                if ($model->getErrors()) {
                    \Evo\Debug::dump($model->getErrors());
                }

            }

            return true;
        }

        return false;

    }




}