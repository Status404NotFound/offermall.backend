<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\modules\rbac\models\Menu */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('user', 'Menus'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="menu-view">




    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="ibox">
                <div class="ibox-title">
                    <h5><?= Html::encode($this->title) ?></h5>
                    <div class="ibox-tools">
                        <?= Html::a(Yii::t('user', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary btn-xs']) ?>
                        <?=
                        Html::a(Yii::t('user', 'Delete'), ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-danger btn-xs',
                            'data' => [
                                'confirm' => 'Are you sure you want to delete this item?',
                                'method' => 'post',
                            ],
                        ])
                        ?>
                    </div>
                </div>
                <div class="ibox-content">

                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'menuParent.name:text:Parent',
                            'name',
                            'route',
                            'order',
                        ],
                    ]) ?>

                </div>
            </div>
        </div>
    </div>

</div>
