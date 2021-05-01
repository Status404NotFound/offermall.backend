<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model callcenter\models\OperatorConf */

$this->title = $model->operator_id;
$this->params['breadcrumbs'][] = ['label' => 'Operator Confs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="operator-conf-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->operator_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->operator_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'operator_id',
            'operator_group_id',
            'status',
            'sip',
            'channel',
        ],
    ]) ?>

</div>
