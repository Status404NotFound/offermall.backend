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
class Select2Asset extends AssetBundle
{
    public $sourcePath = '@genform/web';
    public $css = [
        'extensions/select2_4.0.3/css/select2.min.css',
    ];
    public $js = [
        'extensions/select2_4.0.3/js/select2.min.js',
    ];
}