<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model callcenter\models\OperatorConf */

$this->title = 'Update Operator Conf: ' . $model->operator_id;
$this->params['breadcrumbs'][] = ['label' => 'Operator Confs', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->operator_id, 'url' => ['view', 'id' => $model->operator_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="operator-conf-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
