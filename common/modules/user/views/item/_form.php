<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\modules\user\rule\RouteRule;
use yii\helpers\Json;
use common\modules\user\traits\AuthManagerTrait;

/* @var $this yii\web\View */
/* @var $model common\modules\user\models\AuthItem */
/* @var $form yii\widgets\ActiveForm */
/* @var $context common\modules\user\components\ItemController */

$context = $this->context;
$labels = $context->labels();
$rules = AuthManagerTrait::authManager()->getRules();
unset($rules[RouteRule::RULE_NAME]);
$source = Json::htmlEncode(array_keys($rules));

$js = <<<JS
    $('#rule_name').autocomplete({
        source: $source,
    });
JS;
$this->registerJs($js);
?>

<div class="auth-item-form">
    <?php $form = ActiveForm::begin(['id' => 'item-form']); ?>
    <div class="row">
        <div class="col-sm-6">
            <?= $form->field($model, 'name')->textInput(['maxlength' => 64]) ?>

            <?= $form->field($model, 'description')->textarea(['rows' => 2]) ?>
        </div>
        <div class="col-sm-6">
            <?= $form->field($model, 'ruleName')->textInput(['id' => 'rule_name']) ?>

            <?php echo $form->field($model, 'data')->textarea(['rows' => 6]) ?>
<!--            --><?php //if ($model->isNewRecord) {
//                echo $form->field($model, 'data')->textarea(['rows' => 6]);
//            } else {
//                echo '<label>Data</label><pre>' . $model->data . '</pre>';
//            }
//            ?>
        </div>
    </div>
    <div class="form-group">
        <?php
        echo Html::submitButton($model->isNewRecord ? Yii::t('user', 'Create') : Yii::t('user', 'Update'), [
            'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary',
            'name' => 'submit-button'])
        ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
