<?php

namespace Evo\Helper\View\Field\Form;

use Evo;
use Evo\Rule;
use Evo\Helper\View\Field\FormField;
use Evo\Helper\View\Model as ViewModel;

class Editor extends FormField
{
    protected $rule = null;
    protected $template = '<div class="form-row tinymce">{label} {html} {error}</div>';
    protected $relative = false;

    public $folder = 'upload/editor';
    public $url = '';
    public $height = 300;

    public $contentCss = '';
    public $selectorCss = '';

    /*public function __construct(ViewModel $model, array $config) {
        parent::__construct($model, $config);

        \Evo\Debug::dump($config, false);

    }*/


    // TODO configure by rules
    protected function html()
    {
        $langId = strtolower(Evo::app()->lang->getLocale());

        Evo::app()->view->registerScriptSrc('/asset/tinymce/tinymce.min.js', 'jquery', 'tinymce-js');
        //Evo::app()->view->addHtml(Evo::app()->view->getSource('/asset/elFinder/loader.html'), 'elFinder');

        if(!$this->url) {
            $this->url = $this->locator->absoluteRoute($this->folder, [], '');
            $this->relative = true;
        }

        $source = "
        
            function elFinderBrowser$this->id (field_name, url, type, win) {
                window.tinyMCE.activeEditor.windowManager.open(
                    {
                        file: '/asset/tinymce/elfinder.php?langId=$langId&folder=$this->folder&url=$this->url',
                        title: '{$this->lang->t('common.file_browser')}',
                        width: 900,
                        height: 450,
                        resizable: 'yes'
                    },{
                        setUrl: function (url) {
                            win.document.getElementById(field_name).value = url.replace(/\\\/g, '/');
                        }
                    }
                );
                return false;
            }

            tinyMCE.init({
                fontsize_formats: '8px 10px 12px 14px 18px 24px 36px',
                mode: 'none',
                language: '$langId',
                height: {$this->height},
                file_browser_callback : elFinderBrowser$this->id,
                extended_valid_elements:'script[language|type|src],span',
                remove_trailing_brs: false,
                relative_urls: false,
                convert_urls: false,
                remove_script_host : false,
                setup: function (editor) {
                    editor.on('change', function () {
                        editor.save();
                    });
                },
                plugins: [
                    'advlist autolink lists link image charmap print preview anchor',
                    'searchreplace visualblocks code fullscreen',
                    'insertdatetime media table contextmenu paste code textcolor'
                ],
                toolbar: 'insertfile undo redo | styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | forecolor fontsizeselect',
                content_css: '$this->contentCss',
                body_class: '$this->selectorCss'
            });

            tinyMCE.editors = [];

            tinyMCE.execCommand('mceAddEditor', true, '{$this->inputAttributes['id']}');
           
        ";
        $this->registerInlineScript($source, 'tinymce-js', 'tiny-mce-'.$this->inputAttributes['id']);

        unset($this->inputAttributes['value']);


        return $this->textarea($this->value, $this->inputAttributes);
    }
}