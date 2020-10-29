<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 02.03.16
 * Time: 12:05
 */

namespace Evo\Helper\View;

use Evo;
use Evo\Pagination as Base;
use Evo\View;

class Pagination extends View
{

    protected $pagination = null;
    protected static $counter = 1;


    public function __construct(Base $pagination)
    {
        parent::__construct();

        $this->pagination = $pagination;
        static::$counter++;
    }



    public function getAjax($pageClass='pagination', $template='', $useFilters = true, $pushState=true)
    {
        if(!$template) {
            $template = 'group.left,sibling.left,items,sibling.right,group.right';
        }

        $buttons = $this->pagination->getButtons($template);

        $return = [];

        $buttonCounter = 0;

        $data = [];
        /*if($useFilters) {

            foreach($this->pagination->model->getFields() as $field) {
                foreach($field->getFilter() as $filter) {
                    $data[$filter->getName()] = $filter->value;
                }
            }
        }*/

        foreach($buttons as $key => $value) {
            if ($key != 'items') {
                $class = 'paginate-'.str_replace('.', '_', $key);
                $part = explode('.', $key);
                $inner = $part[1] == 'left' ? '<' : '>';
                if($part[0] == 'group') $inner .= $inner;
                if($value) {

                    $return[] = [
                        'url' => $this->locator->route($this->request->route(), [$this->pagination->url() => $value], null, true),
                        'innerHtml' => $inner,
                        'class' => 'paginate-item ' . $class . (!$value ? ' inactive' : '')
                    ];

                    /*$return[] = $this->ajax()->url()->pushState($pushState)->block()->a($inner, ['class' => 'paginate-item ' . $class . (!$value ? ' inactive' : '')]);*/
                }
            } else {
                $value = (array)$value;
                foreach($value as $v) {
                    if($v) {
                        $return[] = [
                            'url' => $this->locator->route($this->request->route(), [$this->pagination->url() => $v], null, true),
                            'innerHtml' => $v,
                            'class' => 'paginate-item' . ($this->pagination->index() == $v ? ' active' : ''),
                        ];

                        $buttonCounter++;
                        /*$return[] = $this->ajax()->url(
                            $this->locator->route($this->request->route(), array_merge(Evo::app()->request->get(null, null, true), [$this->pagination->url() => $v])
                            ))->pushState($pushState)->block()->a($v, ['class' => 'paginate-item' . ($this->pagination->index() == $v ? ' active' : '')]);*/
                    }
                }
            }
        }

        if($buttonCounter > 1) {
            foreach($return as $key => $item) {
                $return[$key] = $this->ajax()->url($item['url'])
                    ->pushState($pushState)
                    ->block()
                    ->refresh()
                    ->a($item['innerHtml'], ['class' => $item['class']]);
            }
            return $this->div(implode(PHP_EOL, $return), ['class' => $pageClass]);
        }

        return '';

    }





    public function get($pageClass='pagination', $template='')
    {
        if(!$template) {
            $template = 'group.left,sibling.left,items,sibling.right,group.right';
        }

        $buttons = $this->pagination->getButtons($template);

        $return = [];
        foreach($buttons as $key => $value) {
            if ($key != 'items') {
                $class = 'paginate-'.str_replace('.', '_', $key);
                $part = explode('.', $key);
                $inner = $part[1] == 'left' ? '<' : '>';
                if($part[0] == 'group') $inner .= $inner;

                $params = ['class' => 'paginate-item ' . $class . (!$value ? ' inactive' : '')];
                if($value) {
                    $params['href'] = Evo::app()->locator->route(Evo::app()->request->route(), array_merge(Evo::app()->request->get(), [$this->pagination->url() => $value]));
                }
                $return[] = $this->a(htmlspecialchars($inner), $params);

            } else {
                foreach($value as $v) {
                    $params = [
                        'class' => 'paginate-item'. ($this->pagination->index() == $v ? ' active' : ''),
                        'href' => Evo::app()->locator->route(Evo::app()->request->route(), array_merge(Evo::app()->request->get(), [$this->pagination->url() => $v]))
                    ];
                    $return[] = $this->a(htmlspecialchars($v), $params);
                }
            }
        }
        return $this->div(implode(PHP_EOL, $return), ['class' => $pageClass]);
    }

    public function getAddAjaxButton($text, $class='ajax-link', $useFilters=true, $tagName = 'a', $pushState = true)
    {
        $buttons = $this->pagination->getButtons('sibling.right');

        if(!$button = $buttons['sibling.right']) {
            return '';
        }

        if(strpos($text, '{counter}') !== false) {
            $counter = (int)$this->pagination->itemLast() - (int)$this->pagination->itemIndex();
            $text = str_replace('{counter}', $counter, $text);
        }

        $id = $this->pagination->model->className().'PaginationButton_'.static::$counter;


        //echo $this->locator->route(null, [$this->pagination->url() => $button], true);

        $ajax = $this->ajax()
            ->url($this->locator->route(Evo::app()->request->route(), [$this->pagination->url() => $button], true));

        if($pushState) {
            $ajax->pushState();
        }


        /*if($useFilters) {
            $data = [];
            foreach($this->pagination->model->getFields() as $field) {
                foreach($field->getFilter() as $filter) {
                    $data[$filter->getName()] = $filter->value;
                }
            }
            $ajax->data($data);
        }*/


        return $ajax->$tagName($text, ['class' => $class, 'id' => $id]);
    }


    public function pageAjaxWidth(array $counts, $label='', $class='pager-count')
    {
        $options = [];
        foreach($counts as $value => $count) {
            $params = ['value' => !preg_match('/^\d+$/', $value) ? $value : $count];
            if($this->pagination->getLimit() == $count) {
                $params['selected'] = 'selected';
            }
            $options[] = $this->option($count, $params);
        }
        $id = $this->pagination->model->className().'PagerCount_'.static::$counter;


        return $this->form($this->label($label, ['for' => $id]) . PHP_EOL . $this->ajax()->data(['page_width' => $this->js('$(this).val()')])->refresh()->block()->select(implode(PHP_EOL, $options), ['id' => $id, 'name' => 'page_width']), ['class' => $class]);
    }

}