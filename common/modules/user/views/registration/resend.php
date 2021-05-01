<?php


use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var \common\modules\user\models\forms\ResendForm $model
 */

$this->title = Yii::t('user', 'Request new confirmation message');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="passwordBox animated fadeInDown">
    <div class="row">

        <div class="col-md-12">
            <div class="ibox-content">

                <h2 class="font-bold"><?= Html::encode($this->title) ?></h2>

                <p>
                    Enter your email address and you will be sent an email to confirm your account.
                </p>

                <div class="row">

                    <div class="col-lg-12">
                        <?php $form = ActiveForm::begin([
                            'id' => 'resend-form',
                            'enableAjaxValidation' => true,
                            'enableClientValidation' => false,
                        ]); ?>

                        <?= $form->field($model, 'email')
                            ->textInput(['autofocus' => true, 'placeholder' => 'Email address'])->label(false) ?>

                        <?= Html::submitButton(Yii::t('user', 'Continue'),
                            ['class' => 'btn btn-primary block full-width m-b']) ?><br>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <p class="m-t text-center"> <small>Crmka &copy; <?= date('Y') ?></small> </p>
</div>Crmka
