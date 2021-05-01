<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\modules\user\models\AuthItem */
/* @var $context common\modules\user\components\ItemController */

$context = $this->context;
$labels = $context->labels();
$this->title = Yii::t('user', 'Create ' . $labels['Item']);
$this->params['breadcrumbs'][] = ['label' => Yii::t('user', $labels['Items']), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="auth-item-create">
    <h1><?= Html::encode($this->title) ?></h1>
    <?=
    $this->render('_form', [
        'model' => $model,
    ]);
    ?>

</div>
