<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model callcenter\models\OperatorActivitySearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="operator-activity-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'operator_id') ?>

    <?= $form->field($model, 'operator_status') ?>

    <?= $form->field($model, 'status_time_start') ?>

    <?= $form->field($model, 'status_time_finish') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
