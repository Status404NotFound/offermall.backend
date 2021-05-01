<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;


/**
 * @var \yii\web\View $this
 * @var \yii\data\ActiveDataProvider $dataProvider
 * @var \common\modules\user\models\search\UserSearch $searchModel
 */

$this->title = Yii::t('user', 'Manage users');
$this->params['breadcrumbs'][] = $this->title;
?>

<?php Pjax::begin(['id' => 'users-pjax']) ?>

<div class="wrapper wrapper-content animated fadeInRight ecommerce">

    <div class="ibox">
        <div class="ibox-title">
            <h5><?= Html::encode($this->title) ?></h5>
            <div class="ibox-tools">
                <a href="<?= Url::toRoute(['/user/admin/create'])?>" class="btn btn-primary btn-xs">Create new user</a>
            </div>
        </div>
        <div class="ibox-content">
<!--                --><?php //echo $this->render('_search', ['model' => $searchModel]) ?>

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
                        [
                            'value' => function ($model) {
                                if ( empty($model->isOnline) ) {
                                    return '<i class="fa fa-user"> </i>';
                                }
                                return '<i class="fa fa-user text-info"> </i>';
                            },
                            'contentOptions' => ['class' => 'contact-type'],
                            'format' => 'html',
                        ],
                        'profile.name',
                        [
                            'attribute' => 'username',
                            'header' => 'Login',
                        ],
                        [
                            'value' => function() {
                                return '<i class="fa fa-envelope"> </i>';
                            },
                            'contentOptions' => ['class' => 'contact-type'],
                            'format' => 'html',
                        ],
                        'email:email',
                        [
                            'attribute' => 'parent',
                            'format' => 'raw',
                            'value' => function ($model) {
                                if ( empty($model->parent) ) {
                                    return 'None';
                                }
                                return Html::a(
                                    '<span class="label"><i class="fa fa-eye"></i> ' . $model->parent->parent_name . '</span>',
                                    Url::toRoute(['/user/admin/update', 'id' => $model->parent->id])
                                );
                            },
                        ],
                        [
                            'attribute' => 'children',
                            'format' => 'raw',
                            'value' => function ($model) {
                                $arrChildren = $model->children;

                                if ( empty($arrChildren) || !is_array($arrChildren)) {
                                    return 'Not found';
                                }
                                $size = count($arrChildren) ;
                                $res = '';
                                for ( $index = 0; $index < $size; $index++ ) {
                                    $res .= Html::a(
                                        '<span class="label"><i class="fa fa-eye"></i> ' . $arrChildren[$index]->children_name . '</span>',
                                        Url::toRoute(['/user/admin/update', 'id' => $arrChildren[$index]->id]),
                                        [
                                            'class' => ' block p-xxs'
                                        ]
                                    );
                                }
                                return $res;
//                                    return '<pre>' . print_r($arrChildren, true) . '</pre>';
                            },
                        ],
                        'profile.location',
                        [
                            'attribute' => 'last_login_at',
                            'value' => function ($model) {
                                if (!$model->last_login_at || $model->last_login_at == null) {
                                    return Yii::t('user', 'Never');
                                }
                                return $model->last_login_at;
                            },
                        ],
                        [
                            'header' => Yii::t('user', 'Confirmation'),
                            'value' => function ($model) {
                                if ($model->isConfirmed) {
                                    return '<div class="text-center">
                                            <span class="text-success">' . Yii::t('user', 'Confirmed') . '</span></div>';
                                } else {
                                    return Html::a(Yii::t('user', 'Confirm'), ['confirm', 'id' => $model->id], [
                                        'class' => 'btn btn-xs btn-success btn-block',
                                        'data-method' => 'post',
                                        'data-confirm' => Yii::t('user', 'Are you sure you want to confirm this user?'),
                                    ]);
                                }
                            },
                            'format' => 'raw',
                        ],
                        [
                            'header' => Yii::t('user', 'Block status'),
                            'value' => function ($model) {
                                if ($model->isBlocked) {
                                    return Html::a(Yii::t('user', 'Unblock'), ['block', 'id' => $model->id], [
                                        'class' => 'btn btn-xs btn-success btn-block',
                                        'data-method' => 'post',
                                        'data-confirm' => Yii::t('user', 'Are you sure you want to unblock this user?'),
                                    ]);
                                } else {
                                    return Html::a(Yii::t('user', 'Block'), ['block', 'id' => $model->id], [
                                        'class' => 'btn btn-xs btn-danger btn-block',
                                        'data-method' => 'post',
                                        'data-confirm' => Yii::t('user', 'Are you sure you want to block this user?'),
                                    ]);
                                }
                            },
                            'format' => 'raw',
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{switch} {resend_password} {update} {delete}',
                            'buttons' => [
                                'resend_password' => function ($url, $model, $key) {
                                    if (!$model->isAdmin) {
                                        return '
                                            <a data-method="POST" data-confirm="' . Yii::t('user', 'Are you sure?') . '" href="' . Url::to(['resend-password', 'id' => $model->id]) . '">
                                            <span title="' . Yii::t('user', 'Generate and send new password to user') . '" class="glyphicon glyphicon-envelope">
                                            </span> </a>';
                                    }
                                    return null;
                                },
                                'switch' => function ($url, $model) {
                                    if($model->id != Yii::$app->user->id && Yii::$app->getModule('user')->enableImpersonateUser) {
                                        return Html::a('<span class="glyphicon glyphicon-user"></span>', ['/user/admin/switch', 'id' => $model->id], [
                                            'title' => Yii::t('user', 'Become this user'),
                                            'data-confirm' => Yii::t('user', 'Are you sure you want to switch to this user for the rest of this Session?'),
                                            'data-method' => 'POST',
                                        ]);
                                    }
                                }
                            ]
                        ],
                    ],
                ]); ?>

        </div>
    </div>


</div>

<?php Pjax::end() ?>
