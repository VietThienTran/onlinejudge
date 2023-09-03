<?php

namespace app\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        'js/katex/katex.min.css',
        'js/highlight/styles/monokai-sublime.css',
    ];
    public $js = [
        'js/highlight/highlight.pack.js',
        'js/katex/katex.min.js',
        'js/socket.io.js',
        'js/clipboard.min.js',
        'js/app.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
