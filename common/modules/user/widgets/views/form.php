<?php


use yii\bootstrap\Alert;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/**
 * @var $model \common\modules\user\models\Assignment
 */

?>

<?php if ($model->updated): ?>

<?= Alert::widget([
    'options' => [
        'class' => 'alert-success'
    ],
    'body' => Yii::t('user', 'Assignments have been updated'),
]) ?>

<?php endif ?>

<?php $form = ActiveForm::begin([
    'enableClientValidation' => false,
    'enableAjaxValidation'   => false,
    'layout' => 'horizontal',
    'fieldConfig' => [
        'horizontalCssClasses' => [
            'wrapper' => 'col-sm-9',
        ],
    ],
]) ?>

<?= Html::activeHiddenInput($model, 'user_id') ?>

<?= $form->field($model, 'items')->dropDownList(
    $model->getAvailableItems(),
    [
        'placeholder' => "Select a state",
        'id' => 'items',
        'multiple' => false
    ]
)->label( Yii::t('user', 'Role') );?>

<?= Html::submitButton(Yii::t('user', 'Update assignments'), ['class' => 'btn btn-success btn-block']) ?>

<?php ActiveForm::end() ?>

