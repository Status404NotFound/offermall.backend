<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model callcenter\models\OperatorConf */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="operator-conf-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'operator_group_id')->textInput() ?>

    <?= $form->field($model, 'status')->textInput() ?>

    <?= $form->field($model, 'sip')->textInput() ?>

    <?= $form->field($model, 'channel')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
