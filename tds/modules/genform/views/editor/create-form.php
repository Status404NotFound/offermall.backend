<?php
use yii\bootstrap\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
?>

<div id="wrapper" class="">
    <?= Html::beginForm(Yii::$app->request->url == \yii\helpers\Url::toRoute(['create-form']), 'post') ?>
    <div class="container">
        <h1><b>Offer:</b> <?= $offer_name ?></h1>
        <?= Html::textarea('form-name', '', [
            'style'=>'width:100%;',
            'rows'=>'2',
            'value'
        ]) ?>
        <?= Html::hiddenInput('offer_id', $offer_id, []) ?>
    </div>
    <div class="container">
        <?= Html::submitButton('Create form', ['class' => 'btn btn-success']) ?>
    </div>
    <?= Html::endForm() ?>
</div>
