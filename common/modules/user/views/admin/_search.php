<?php

use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\modules\user\models\tables\UserS */
/* @var $form yii\widgets\ActiveForm */
?>

<!--<div class="row">-->

    <?php $form = ActiveForm::begin([
//        'layout' => 'horizontal',
        'action' => ['index'],
        'method' => 'get',
    ]); ?>
    <div class="col-sm-6">
        <?= $form->field($model, 'username')->textInput(['onchange'=>'this.form.submit()']) ?>
    </div>

    <div class="col-sm-6">
        <?= $form->field($model, 'email')->textInput(['onchange'=>'this.form.submit()']) ?>
    </div>

<!--    <div class="col-sm-3">-->
        <?php  //echo $form->field($model, 'registration_ip') ?>
<!--    </div>-->

<!--    <div class="col-sm-3">-->
        <?php  //echo $form->field($model, 'last_login_at') ?>
<!--    </div>-->

    <?php ActiveForm::end(); ?>

<!--</div>-->