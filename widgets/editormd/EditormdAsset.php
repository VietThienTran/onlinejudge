<?php
namespace app\widgets\editormd;

use yii\web\AssetBundle;

class EditormdAsset extends AssetBundle
{
    public $sourcePath = '@app/widgets/editormd/assets';
    public $js = [
        'editormd.min.js'
    ];
    public $css = [
        'css/editormd.min.css',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
