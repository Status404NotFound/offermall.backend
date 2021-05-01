<?php

namespace callcenter\assets;

use yii\web\AssetBundle;

/**
 * Main callcenter application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        'css/auto-mode/auto-mode.css',
    ];
    public $js = [
        '//code.jquery.com/ui/1.11.2/jquery-ui.js',
        'js/functions.js',
        'js/auto-mode/auto.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
