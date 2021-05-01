<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel callcenter\models\OperatorConfSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Operator Confs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="operator-conf-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Operator Conf', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'operator_id',
            'operator_group_id',
            'status',
            'sip',
            'channel',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
