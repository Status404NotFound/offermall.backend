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
class DuallistboxAsset extends AssetBundle
{
    public $sourcePath = '@common/theme/inspinia/assets';
    public $css = [
        'css/plugins/dualListbox/bootstrap-duallistbox.min.css'
    ];
    public $js = [
        'js/plugins/dualListbox/jquery.bootstrap-duallistbox.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
