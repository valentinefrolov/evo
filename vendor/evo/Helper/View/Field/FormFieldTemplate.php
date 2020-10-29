<?php


namespace Evo\Helper\View\Field;

use Evo;
use Evo\Helper\View\Field\ModelField;
use Evo\Helper\View\Interfaces\IFormField;

class FormFieldTemplate implements IFormField
{
    private $field = null;
    function getWrapper(): string
    {
        return '<div class="form-row">{label}{html}{hint}{error}</div>';
    }

    function getLabel(): string
    {
        return Evo\Html::label($this->field->title, ['for' => $this->field->id]);
    }

    public function __construct(ModelField $field)
    {
        $this->field = $field;
    }

    function getError(array $errors): string
    {
        $err = '';
        foreach($errors as $error) {
            $err .= "<p class=\"error\">$error</p>".PHP_EOL;
        }
        return $err;
    }
}