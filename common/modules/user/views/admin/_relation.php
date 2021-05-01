<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var common\modules\user\models\tables\User $user
 * @var \common\modules\user\models\forms\RelationsForm $modelParent
 */
?>

<?php $this->beginContent('@common/modules/user/views/admin/update.php', ['user' => $user]) ?>

<?php $form = ActiveForm::begin([
    'layout' => 'horizontal',
    'enableAjaxValidation' => true,
    'enableClientValidation' => false,
    'fieldConfig' => [
        'horizontalCssClasses' => [
            'wrapper' => 'col-sm-9',
        ],
    ],
]); ?>
<?= $form->field($modelParent, 'parent_name', [
    'inputOptions' => [
        'placeholder' => 'Parent',
        'autofocus' => 'autofocus',
        'class' => 'form-control',
        'tabindex' => '1']])->dropDownList(array_merge(['' => 'None'],$modelParent->usersList()));
?>

<div class="form-group">
    <div class="col-lg-offset-3 col-lg-9">
        <?= Html::submitButton(Yii::t('user', 'Update'), ['class' => 'btn btn-block btn-success']) ?>
    </div>
</div>

<?php ActiveForm::end(); ?>

<?php $this->endContent() ?>
