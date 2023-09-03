<?php
namespace app\widgets\codemirror;

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\InputWidget;

class CodeMirror extends InputWidget
{
    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $options = ArrayHelper::merge($this->options, ['style' => 'display:none']);
        if ($this->hasModel()) {
            echo Html::activeTextArea($this->model, $this->attribute, $options);
        } else {
            echo Html::textArea($this->name, $this->value, $options);
        }
        $this->registerScripts();
    }

    public function registerScripts()
    {
        CodeMirrorAsset::register($this->view);
        $id = $this->options['id'];
        $script = <<<EOF
        CodeMirror.fromTextArea(document.getElementById("{$id}"),{
            theme: "darcula",
            lineNumbers: true,
            styleActiveLine: true,
            indentUnit: 4,
            autofocus: true,
            matchBrackets: true,
            autoRefresh: true,
            lineWrapping: true, 
            foldGutter: true,
            autoCloseBrackets: true
        });
EOF;
        $this->view->registerCss("
        .CodeMirror {
            border: 1px solid black;
            font-size: 15px;
        }
        ");
        $this->view->registerJs($script);
    }
}
