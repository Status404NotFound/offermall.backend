<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel callcenter\models\OperatorActivitySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Operator Activities';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="operator-activity-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Operator Activity', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'operator_id',
            'operator_status',
            'status_time_start',
            'status_time_finish',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
