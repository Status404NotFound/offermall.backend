<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model callcenter\models\OperatorActivity */

$this->title = 'Update Operator Activity: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Operator Activities', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="operator-activity-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
