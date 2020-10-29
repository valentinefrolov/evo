<?php
/**
 * Created by PhpStorm.
 * User: frolov
 * Date: 01.03.16
 * Time: 14:09
 */

namespace Evo\Helper\View\Field\Table;

use Evo\Helper\View\Field\TableField;

class Image extends TableField
{
    protected $href = '';
    protected $replacements = [];

    protected function html()
    {
        if(!$this->value)
            return $this->lang->t('common.no_image');
        return $this->img(['src' => $this->value, 'alt' => $this->title]);
    }

}