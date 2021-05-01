<?php


use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var \common\modules\user\models\tables\User $model
 * @var \common\modules\user\Module $module
 */

$this->title = Yii::t('user', 'Sign up');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="middle-box text-center loginscreen   animated fadeInDown">
    <div>
        <div>
            <h1 class="logo-name">AF</h1>
        </div>
        <h3>Register to Crmka</h3>
        <p>Create account </p>
        <?php $form = ActiveForm::begin([
            'id' => 'registration-form',
            'enableAjaxValidation' => true,
            'enableClientValidation' => false,
        ]); ?>
            <?= $form->field($model, 'username')->textInput(['placeholder' => 'Name'])->label(false) ?>

            <?= $form->field($model, 'email')->textInput(['placeholder' => 'Email'])->label(false) ?>


            <?php if ($module->enableGeneratingPassword == false): ?>
                <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Password'])->label(false) ?>
            <?php endif ?>

            <?= Html::submitButton(Yii::t('user', 'Register'), ['class' => 'btn btn-primary block full-width m-b']) ?>

            <p class="text-muted text-center"><small>Already have an account?</small></p>
            <?= Html::a(Yii::t('user', 'Login'), ['/user/security/login'], ['class' => 'btn btn-sm btn-white btn-block']) ?>
        <?php ActiveForm::end(); ?>
        <p class="m-t"> <small>Crmka &copy; <?= date('Y') ?></small> </p>
    </div>
</div>


