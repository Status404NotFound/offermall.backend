<?php

namespace tds\modules\genform\controllers;

use tds\modules\genform\models\BlockFields;
use tds\modules\genform\models\builder\TestForm;
use tds\modules\genform\models\Fields;
use Fxp\Composer\AssetPlugin\Assets;
use yii\helpers\Html;
use yii\web\AssetBundle;
use yii\web\Controller;

/**
 * Default controller for the `genform` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $modelForm = new TestForm(0, new BlockFields());
        $form = $modelForm->renderHTML();
        $form .= Html::style($modelForm->renderCSS());

        $this->view->registerCssFile('http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/less/mixins/grid-framework.less');
        echo Html::tag('div', $form, ['class' => 'form-wrapper']);
//        return $this->renderAjax('index', ['form' => $form]);
    }
}
