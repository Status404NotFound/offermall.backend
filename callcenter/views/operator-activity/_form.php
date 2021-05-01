<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model callcenter\models\OperatorActivity */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="operator-activity-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'operator_id')->textInput() ?>

    <?= $form->field($model, 'operator_status')->textInput() ?>

    <?= $form->field($model, 'status_time_start')->textInput() ?>

    <?= $form->field($model, 'status_time_finish')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
