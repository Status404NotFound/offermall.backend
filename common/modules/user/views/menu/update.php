<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\rbac\models\Menu */

$this->title = Yii::t('user', 'Update Menu') . ': ' . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('user', 'Menus'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('user', 'Update');
?>
<div class="menu-update">

    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="ibox">
                <div class="ibox-title">
                    <h5><?= Html::encode($this->title) ?></h5>
                </div>
                <div class="ibox-content">

                    <?= $this->render('_form', [ 'model' => $model ])?>

                </div>
            </div>
        </div>
    </div>

</div>
