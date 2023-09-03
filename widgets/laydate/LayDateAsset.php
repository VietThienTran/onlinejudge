<?php
namespace app\widgets\laydate;

use yii\web\AssetBundle;

class LayDateAsset extends AssetBundle
{
    public $sourcePath = '@app/widgets/laydate/assets';

    public $js = [
        'laydate.js',
    ];

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
