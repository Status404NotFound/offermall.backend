<?php

use yii\bootstrap\Nav;
use yii\helpers\Html;

/**
 * @var \yii\web\View $this
 * @var \common\modules\user\models\tables\User $user
 * @var string $content
 */
$this->title = Yii::t('user', 'Update user account');
$this->params['breadcrumbs'][] = ['label' => Yii::t('user', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$directoryAsset = Yii::$app->assetManager->getPublishedUrl('@common/theme/inspinia/assets');
?>

<div class="row  m-b-lg m-t-lg">
    <div class="col-md-12">
        <div class="profile-image">
            <img src="<?= $directoryAsset ?>/img/default-user-image.png" class="img-circle circle-border m-b-md" alt="profile">
            <div class="btn-group-vertical full-width m-xxs">
                <?php if (!$user->isConfirmed ):?>
                    <?= Html::a('<i class="fa fa-check"></i> ' . Yii::t('user', 'Confirm'),
                        ['/user/admin/confirm', 'id' => $user->id],[
                            'class' => 'btn btn-success btn-xs',
                            'data-method' => 'post',
                            'data-confirm' => Yii::t('user', 'Are you sure you want to confirm this user?'),
                        ]) ?>
                <?php endif; ?>

                <?php if ( $user->isBlocked ):?>
                    <?= Html::a('<i class="fa fa-ban"></i> ' . Yii::t('user', 'Unblock'),
                        ['/user/admin/block', 'id' => $user->id],[
                            'class' => 'btn btn-primary btn-xs',
                            'data-method' => 'post',
                            'data-confirm' => Yii::t('user', 'Are you sure you want to unblock this user?'),
                        ]) ?>
                <?php else:?>
                    <?= Html::a('<i class="fa fa-ban"></i> ' . Yii::t('user', 'Block'),
                        ['/user/admin/block', 'id' => $user->id],[
                            'class' => 'btn btn-warning btn-xs',
                            'data-method' => 'post',
                            'data-confirm' => Yii::t('user', 'Are you sure you want to block this user?'),
                        ]) ?>
                <?php endif; ?>

                <?= Html::a('<i class="fa fa-trash"></i> ' . Yii::t('user', 'Delete'),
                    ['/user/admin/delete', 'id' => $user->id],[
                        'class' => 'btn btn-danger btn-xs',
                        'data-method' => 'post',
                        'data-confirm' => Yii::t('user', 'Are you sure you want to delete this user?'),
                    ]) ?>
            </div>
        </div>
        <div class="profile-info">
            <h1 class="m-xs">
                <?= $user->username ?>
            </h1>
            <?= $this->render('_info', ['user' => $user]) ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="panel panel-default">
            <div class="panel-body">
                <?= Nav::widget([
                    'options' => [
                        'class' => 'nav-pills nav-stacked',
                    ],
                    'items' => [
                        [
                            'label' => Yii::t('user', 'Account details'),
                            'url' => ['/user/admin/update', 'id' => $user->id]
                        ],
                        [
                            'label' => Yii::t('user', 'Profile details'),
                            'url' => ['/user/admin/update-profile', 'id' => $user->id]
                        ],
                        [
                            'label' => Yii::t('user', 'Relations'),
                            'url' => ['/user/admin/relations', 'id' => $user->id],
                            'visible' => 1,
                        ],
                        '<hr>',
                        [
                            'label' => Yii::t('user', 'Assignments'),
                            'url' => ['/user/admin/assignments', 'id' => $user->id],
                        ],
                    ],
                ]) ?>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="panel panel-default">
            <div class="panel-body">
                <?= $content ?>
            </div>
        </div>
    </div>
</div>
