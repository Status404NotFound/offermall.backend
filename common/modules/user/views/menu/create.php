<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\user\models\tables\Menu */

$this->title = Yii::t('user', 'Create Menu');
$this->params['breadcrumbs'][] = ['label' => Yii::t('user', 'Menus'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="menu-create">

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
