<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace tds\modules\genform\assets;

use yii\web\AssetBundle;


/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class GenformAsset extends AssetBundle
{
    public $sourcePath = '@genform/web';
    public $css = [
        'css/bootstrap.css',
        'css/genform.css',
    ];

    public $depends = [
//        'app\assets\AppAsset',
//        'yii\web\YiiAsset',
//        'yii\bootstrap\BootstrapAsset',
        'yii\jui\JuiAsset',
        'tds\modules\genform\assets\Select2Asset',
    ];
}