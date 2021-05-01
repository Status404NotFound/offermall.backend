<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model callcenter\models\OperatorActivity */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Operator Activities', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="operator-activity-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
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
            'id',
            'operator_id',
            'operator_status',
            'status_time_start',
            'status_time_finish',
        ],
    ]) ?>

</div>
