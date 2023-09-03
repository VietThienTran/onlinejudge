<?php
namespace app\widgets\codemirror;

use yii\web\AssetBundle;

class CodeMirrorAsset extends AssetBundle
{
    public $sourcePath = '@app/widgets/codemirror/assets';
    public $js = [
        'lib/codemirror.js',
        'addon/selection/active-line.js',
        'addon/edit/matchbrackets.js',
        'addon/display/autorefresh.js',
        'mode/javascript/javascript.js'
    ];
    public $css = [
        'lib/codemirror.css',
        'theme/darcula.css'
    ];
    public $depends = [
    ];
}
