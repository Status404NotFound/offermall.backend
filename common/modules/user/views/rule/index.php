<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this  yii\web\View */
/* @var $model common\modules\user\models\BizRule */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel common\modules\user\models\search\BizRuleSearch */

$this->title = Yii::t('user', 'Rules');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="role-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('user', 'Create Rule'), ['create'], ['class' => 'btn btn-success']) ?>
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
            ['class' => 'yii\grid\ActionColumn',],
        ],
    ]);
    ?>

</div>
