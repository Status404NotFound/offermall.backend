<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $searchModel common\modules\user\models\search\MenuSearch */

$this->title = Yii::t('user', 'Menus');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="menu-index">

    <div class="ibox">
        <div class="ibox-title">
            <h5><?= Html::encode($this->title) ?></h5>
            <div class="ibox-tools">
                <?= Html::a(Yii::t('user', 'Create Menu'), ['create'], ['class' => 'btn btn-primary btn-xs']) ?>
            </div>
        </div>
        <div class="ibox-content">
            <?php // echo $this->render('_search', ['model' => $searchModel]);  ?>
            <?= GridView::widget([
                'layout' => "{pager}\n{summary}\n{items}",
                'pager' => ['options' => ['class' => 'pagination pull-right']],
                'summaryOptions' => ['class' => 'pagination pull-left m-n'],
                'dataProvider' => $dataProvider,
//                        'filterModel'  => $searchModel,
//                        'layout'       => "{items}\n{pager}",
                'tableOptions' => [
                    'class' => 'table table-striped table-hover m-b-xs',
                ],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'name',
                    [
                        'attribute' => 'menuParent.name',
                        'filter' => Html::activeTextInput($searchModel, 'parent_name', [
                            'class' => 'form-control', 'id' => null
                        ]),
                        'label' => Yii::t('user', 'Parent'),
                    ],
                    'route',
                    'order',
                    ['class' => 'yii\grid\ActionColumn'],
                ],
            ]); ?>

        </div>
    </div>

</div>
