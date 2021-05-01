<?php

use common\modules\user\traits\AuthManagerTrait;
use yii\helpers\Html;
use yii\grid\GridView;
use common\modules\user\rule\RouteRule;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel common\modules\user\models\search\AuthItemSearch */
/* @var $context common\modules\user\components\ItemController */

$context = $this->context;
$labels = $context->labels();
$this->title = Yii::t('user', $labels['Items']);
$this->params['breadcrumbs'][] = $this->title;

$rules = array_keys(AuthManagerTrait::authManager()->getRules());
$rules = array_combine($rules, $rules);
unset($rules[RouteRule::RULE_NAME]);
?>
<div class="role-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <p>
        <?= Html::a(Yii::t('user', 'Create ' . $labels['Item']), ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'name',
                'label' => Yii::t('user', 'Name'),
            ],
            [
                'attribute' => 'ruleName',
                'label' => Yii::t('user', 'Rule Name'),
                'filter' => $rules
            ],
            [
                'attribute' => 'description',
                'label' => Yii::t('user', 'Description'),
            ],
            ['class' => 'yii\grid\ActionColumn',],
        ],
    ])
    ?>

</div>
