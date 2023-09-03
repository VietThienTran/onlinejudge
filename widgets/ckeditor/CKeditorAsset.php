<?php
namespace app\widgets\ckeditor;

use yii\web\AssetBundle;

class CKeditorAsset extends AssetBundle
{
    public $sourcePath = '@app/widgets/ckeditor/assets';
    public $js = [
        'ckeditor.js'
    ];
    public $css = [
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
