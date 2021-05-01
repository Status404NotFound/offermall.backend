<?php

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Nav;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var \common\modules\user\models\tables\User $user
 */

$userName = $model->{$usernameField};

if (!empty($fullnameField)) {
    $userName .= ' (' . \yii\helpers\ArrayHelper::getValue($model, $fullnameField) . ')';
}
$userName = Html::encode($userName);

$this->title = Yii::t('user', 'Assignment') . ' : ' . $userName;
$this->params['breadcrumbs'][] = ['label' => Yii::t('user', 'Assignments'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $userName;
?>

<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <div class="panel panel-default">
            <div class="panel-body">

                <?= \common\modules\user\widgets\Assignments::widget(['model' => $model]) ?>
            </div>
        </div>
    </div>
</div>
