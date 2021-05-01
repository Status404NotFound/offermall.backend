<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace common\theme\inspinia;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Select2Asset extends AssetBundle
{
    public $sourcePath = '@common/theme/inspinia/assets';
    public $css = [
        'css/plugins/select2/select2.min.css'
    ];
    public $js = [
        'js/plugins/select2/select2.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
