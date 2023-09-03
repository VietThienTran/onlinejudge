<?php
namespace app\widgets\ckeditor;

use http\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\InputWidget;

class CKeditor extends InputWidget
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
        CKeditorAsset::register($this->view);
        $id = $this->options['id'];
        $uploadUrl = \yii\helpers\Url::toRoute(['/image/upload']);
        $script = <<<EOF
ClassicEditor.create( document.querySelector('#{$id}'), {
   ckfinder: {
       uploadUrl: "{$uploadUrl}"
   }
})
.then( editor => {
    console.log( editor );
})
.catch( error => {
    console.error( error );
});
EOF;
        $this->view->registerJs($script);
    }
}
